<?php


namespace NoOrm;

use PDO;
use PDOException;

class EntityGenerator
{
    const RELATION_PATTERN = '/FOREIGN KEY.*\(`(.*)`\).*REFERENCES.*`(.*)`.*\(`(.*)`\)/m';

    protected static PDO $connection;
    protected static string $targetFolder;
    protected static string $templatesFolder;

    public static function process(string $host, string $dbname, string $username, string $password,
                               string $targetFolder, string $namespace): ?string
    {

        if ($result = self::setTargetFolder($targetFolder)) {
            return $result;
        }

        if (!self::setTemplatesFolder()) {
            return 'Problem on setTemplatesFolder';
        }

        if (!self::connect($host, $dbname, $username, $password)) {
            return 'Problem connecting to the DB';
        }

        if ($result = self::generateFiles($host, $dbname, $username, $password, $namespace)) {
            return $result;
        }

        return self::generateClasses($namespace);
    }

    protected static function setTargetFolder(string $targetFolder): ?string
    {
        if (!self::createPath($targetFolder)) {
            return 'Problem creating the folder where the classes will be placed';
        }

        if (!self::deleteAllFiles($targetFolder)) {
            return 'Problem delete all files the folder where the classes will be placed';
        }

        self::$targetFolder = $targetFolder;

        return null;
    }

    protected static function createPath(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }

        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);

        return self::createPath($prev_path) && is_writable($prev_path) && mkdir($path);
    }

    protected static function deleteAllFiles(string $path): bool
    {
        $files = glob("$path/*");

        if (empty($files)) {
            return true;
        }

        $max = count($files);
        $i = 0;
        $result = false;
        $file = '';

        while($i < $max) {
            $file = $files[$i];

            if (is_dir($file)) {
                $result = self::deleteAllFiles($file);

                if ($result) {
                    $result = rmdir($file);
                }
            } else {
                $result = unlink($file);
            }

            if ($result) {
                ++$i;
            } else {
                $i = $max;
            }
        }

        return $result;
    }

    protected static function setTemplatesFolder():bool
    {
        self::$templatesFolder = __DIR__ . '/templates/';

        return file_exists(self::$templatesFolder);
    }

    protected static function connect(string $host, string $dbname, string $username, string $password): bool
    {
        $result = true;

        try {
            self::$connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        } catch (PDOException $e) {
            $result = false;
        }

        return $result;
    }

    protected static function generateFiles(string $host, string $dbname, string $username, string $password,
                                            string $namespace): ?string
    {
        if(!self::copyReplace('Connection',
            ['{{namespace}}','{{host}}', '{{dbname}}', '{{username}}', '{{password}}'],
            [$namespace, $host, $dbname, $username, $password])) {

            return 'Problem generating the connection file';
        }

        if(!self::copyReplace('EntityBody', ['{{namespace}}'], [$namespace])) {
            return 'Problem generating the EntityBody file';
        }

        if(!self::copyReplace('SQLGenerator', ['{{namespace}}'], [$namespace])) {
            return 'Problem generating the SQLGenerator file';
        }

        return null;
    }

    protected static function copyReplace(string $fileName, array $search, array $replace): bool
    {
        $content = file_get_contents(self::$templatesFolder . $fileName);

        if(!$content) {
            return false;
        }

        $content = str_replace($search, $replace, $content);

        $result = file_put_contents(self::$targetFolder . $fileName . '.php', $content);

        return (is_numeric($result));
    }

    protected static function generateClasses(string $namespace): ?string
    {

        if(!$tpl = self::getTplContent($namespace)) {
            return 'Problem on generateClasses reading tpl file';
        }

        list($descs, $creates) = self::getTablesInfo();

        if(!$descs) {
            return $creates;
        }

        if(!self::generateEntities($descs, $creates, $namespace, $tpl)) {
            return 'Problem on generateEntities, file can not be saved';
        }

        return null;
    }

    protected static function getTplContent(string $namespace): ?string
    {
        $tpl = file_get_contents(self::$templatesFolder . 'tpl');

        if(!$tpl) {
            return null;
        }

        return str_replace('{{namespace}}', $namespace, $tpl);
    }

    protected static function getTablesInfo(): array
    {
        $descs = [];
        $creates = [];
        $conn = self::$connection;

        $tables = $conn->query('SHOW TABLES');

        if (!$tables) {
            return [false, 'Problem getting tables'];
        }

        foreach ($tables->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $descs[$table] = $conn->query("DESC $table")->fetchAll(PDO::FETCH_ASSOC);
            $creates[$table] = $conn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM)[1];
        }

        return [$descs, $creates];
    }

    protected static function generateEntities(array $descs, array $creates, string $namespace, string $EntityTpl): bool
    {
        $tables = array_keys($descs);
        $max = count($tables);
        $result = false;
        $i = 0;

        while ($i < $max) {
            $table = $tables[$i];
            $desc = $descs[$table];

            $fileName = self::toPascalCase($table);

            $tpl = str_replace(['{{table_name}}', '{{class_name}}'], [$table, $fileName], $EntityTpl);

            $constants = '';
            $classDescription = "/**\n * Class $fileName\n * @package $namespace\n";
            $pascalCasedProperties = "[\n";
            $snakeCasedProperties = "[\n";

            foreach ($desc as $colData) {
                $colName = $colData['Field'];
                $constantName = 'COL_' . strtoupper($colName);
                $pascalProperty = self::toPascalCase($colName);
                $type = self::getTypeFromMysql($colData['Type']);

                $constants .= "    const {$constantName} = '{$colName}';\n";

                $classDescription .= " * @method {$type} get{$pascalProperty}()\n";

                $pascalCasedProperties .= "    '{$pascalProperty}' => '{$colName}',\n";
                $snakeCasedProperties .= "    '{$colName}' => '{$pascalProperty}',\n";
            }

            $classDescription .= ' */';
            $pascalCasedProperties = substr_replace($pascalCasedProperties, '];', strlen($pascalCasedProperties) - 2, 1);
            $snakeCasedProperties = substr_replace($snakeCasedProperties, '];', strlen($snakeCasedProperties) - 2, 1);

            $tpl = str_replace(
                ['{{class_description}}', '{{constants}}', '{{pascal_properties}}', '{{snake_properties}}', '{{relations}}'],
                [$classDescription, $constants, $pascalCasedProperties, $snakeCasedProperties,
                    self::extractRelationships($creates[$table], $table, $namespace)],
                $tpl);

            if ($result = file_put_contents(self::$targetFolder . $fileName . '.php', $tpl)) {
                ++$i;
            } else {
                $i = $max;
            }
        }

        return $result;
    }

    protected static function toPascalCase(string $string): string
    {
        return str_replace('_', '', ucwords($string, '_'));
    }

    protected static function getTypeFromMysql(string $type): string
    {
        if (strpos('int', $type) !== false) return 'integer';
        if (strpos('float', $type) !== false) return 'float';
        if (strpos('double', $type) !== false) return 'double';
        if (strpos('real', $type) !== false) return 'real';
        if (strpos('bit', $type) !== false) return 'boolean';
        if (strpos('boolean', $type) !== false) return 'boolean';
        if (strpos('date', $type) !== false) return 'datetime';
        if (strpos('time', $type) !== false) return 'datetime';

        return 'string';
    }

    protected static function extractRelationships(string $create, string $tableName, string $namespace): string
    {
        $relations = "[\n";
        $matches = [];
        preg_match_all(self::RELATION_PATTERN, $create, $matches, PREG_SET_ORDER, 0);

        if (count($matches)) {
            foreach ($matches as $r) {
                $columnsInfo = $namespace . '\\' . self::toPascalCase($r[2]) . '::getColumnsInfo';
                $relations .= "['tableName' => '{$r[2]}', 'columnsInfo' => '{$columnsInfo}', 'onCol1' => '{$tableName}.{$r[1]}', 'onCol2' => '{$r[2]}.{$r[3]}'],\n";
            }

            return substr_replace($relations, '];', strlen($relations) - 2, 1);
        }

        return '[];';
    }
}