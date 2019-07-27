<?php

DEFINE('CONFIG_FILE', 'config.ini');
DEFINE('SQL_GENERATOR', 'templates/SQLGenerator.php');
DEFINE('SQL_GENERATOR_TARGET', 'Entity/SQLGenerator.php');
DEFINE('TPL_CONNECTION', 'templates/connection');
DEFINE('TPL_CONNECTION_TARGET', 'Entity/Connection.php');
DEFINE('TPL_FILE', 'templates/tpl');
DEFINE('RELATION_PATTERN', '/FOREIGN KEY.*\(`(.*)`\).*REFERENCES.*`(.*)`.*\(`(.*)`\)/m');

function generateConnection($dbData)
{
    $content = file_get_contents(TPL_CONNECTION);
    $content = str_replace(['{{host}}', '{{dbname}}', '{{username}}', '{{password}}'],
        [$dbData['host'], $dbData['dbname'], $dbData['username'], $dbData['password']], $content);
    file_put_contents(TPL_CONNECTION_TARGET, $content);
}

function generateEntities($descs, $creates)
{
    foreach ($descs as $table => $desc) {
        $fileName = toPascalCase($table);
        $tpl = str_replace(['{{table_name}}', '{{class_name}}'], [$table, $fileName], TPL_CONTENT);
        $constants = '';
        $classDescription = "/**\n * Class $fileName\n * @package Entity\n";
        $pascalCasedProperties = "[\n";
        $snakeCasedProperties = "[\n";

        foreach ($desc as $colData) {
            $colName = $colData['Field'];
            $constantName = 'COL_' . strtoupper($colName);
            $pascalProperty = toPascalCase($colName);
            $type = getTypeFromMysql($colData['Type']);

            $constants .= "    const {$constantName} = '{$colName}';\n";

            $classDescription .= " * @method {$type} get{$pascalProperty}()\n * @method void set{$pascalProperty}({$type} \$value)\n";

            $pascalCasedProperties .= "    '{$pascalProperty}' => '{$colName}',\n";
            $snakeCasedProperties .= "    '{$colName}' => '{$pascalProperty}',\n";
        }

        $classDescription .= ' */';
        $pascalCasedProperties = substr_replace($pascalCasedProperties, '];', strlen($pascalCasedProperties) - 2, 1);
        $snakeCasedProperties = substr_replace($snakeCasedProperties, '];', strlen($snakeCasedProperties) - 2, 1);

        $tpl = str_replace(['{{class_description}}', '{{constants}}', '{{pascal_properties}}', '{{snake_properties}}', '{{relations}}'],
            [$classDescription, $constants, $pascalCasedProperties, $snakeCasedProperties, extractRelations($creates[$table], $table)], $tpl);

        file_put_contents('Entity/' . $fileName . '.php', $tpl);
    }
}

function toPascalCase($string)
{
    return str_replace('_', '', ucwords($string, '_'));
}

function getTypeFromMysql($type)
{

    if (strpos('int', $type)) return 'integer';
    if (strpos('float', $type)) return 'float';
    if (strpos('double', $type)) return 'double';
    if (strpos('real', $type)) return 'real';
    if (strpos('bit', $type)) return 'boolean';
    if (strpos('boolean', $type)) return 'boolean';
    if (strpos('date', $type)) return 'datetime';
    if (strpos('time', $type)) return 'datetime';

    return 'string';
}

function extractRelations($create, $tableName)
{
    $relations = "[\n";
    $matches = [];
    preg_match_all(RELATION_PATTERN, $create, $matches, PREG_SET_ORDER, 0);

    if (count($matches)) {
        foreach ($matches as $r) {
            $columnsInfo = '\\Entity\\' . toPascalCase($r[2]) . '::getColumnsInfo';
            $relations .= "['tableName' => '{$r[2]}', 'columnsInfo' => '{$columnsInfo}', 'onCol1' => '{$tableName}.{$r[1]}', 'onCol2' => '{$r[2]}.{$r[3]}'],\n";
        }

        return substr_replace($relations, '];', strlen($relations) - 2, 1);
    }

    return '[];';

}
