<?php

DEFINE('CONFIG_FILE', 'config.ini');
DEFINE('TPL_CONNECTION', 'templates/connection');
DEFINE('TPL_CONNECTION_TARGET', 'Entity/Connection.php');
DEFINE('TPL_FILE', 'templates/tpl');

function generateConnection($dbData)
{
    $content = file_get_contents(TPL_CONNECTION);
    $content = str_replace(['{{host}}', '{{dbname}}', '{{username}}', '{{password}}'],
        [$dbData['host'], $dbData['dbname'], $dbData['username'], $dbData['password']], $content);
    file_put_contents(TPL_CONNECTION_TARGET, $content);
}

function generateEntities($metadata)
{
    foreach ($metadata as $table => $desc) {
        $fileName = toPascalCase($table);
        $tpl = str_replace(['{{table_name}}', '{{class_name}}'], [$table, $fileName], TPL_CONTENT);
        $constants = '';
        $classDescription = "/**\n * Class $fileName\n * @package Entity\n";
        $pascalCasedProperties = "[\n";

        foreach ($desc as $colData) {
            $colName = $colData['Field'];
            $constantName = 'COL_' . strtoupper($colName);
            $pascalProperty = toPascalCase($colName);
            $type = getTypeFromMysql($colData['Type']);

            $constants .= "    const {$constantName} = '{$colName}';\n";

            $classDescription .= " * @method {$type} get{$pascalProperty}()\n * @method void set{$pascalProperty}({$type} \$value)\n";

            $pascalCasedProperties .= "    '{$pascalProperty}' => '{$colName}',\n";
        }

        $classDescription .= ' */';
        $pascalCasedProperties = substr_replace($pascalCasedProperties, '];', strlen($pascalCasedProperties) - 2, 1);

        $tpl = str_replace(['{{class_description}}', '{{constants}}', '{{pascal_properties}}', '{{properties}}'],
            [$classDescription, $constants, $pascalCasedProperties], $tpl);

        file_put_contents('Entity/' . $fileName . '.php', $tpl);
        die;
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
