<?php

include 'helpers.php';


if (!file_exists(CONFIG_FILE)) throw new Exception('Config file does not exist');

$config = parse_ini_file(CONFIG_FILE, true, INI_SCANNER_TYPED);

$outputFolder = $config['output']['folder'];
exec("rm -rf {$outputFolder}");
exec("mkdir {$outputFolder}");

$dbData = $config['db'];
$conn = new PDO("mysql:host={$dbData['host']};dbname={$dbData['dbname']}", $dbData['username'], $dbData['password']);

generateConnection($dbData);

$tables = $conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$metadata = [];

foreach ($tables as $table)
    $metadata[$table] = $conn->query("DESC $table")->fetchAll(PDO::FETCH_ASSOC);

$conn = null;

DEFINE('TPL_CONTENT', file_get_contents(TPL_FILE));

generateEntities($metadata);
