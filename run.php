<?php

include 'helpers.php';


if (!file_exists(CONFIG_FILE)) throw new Exception('Config file does not exist');

$config = parse_ini_file(CONFIG_FILE, true, INI_SCANNER_TYPED);

$outputFolder = $config['output']['folder'];
exec("rm -rf {$outputFolder}");
exec("mkdir {$outputFolder}");

$dbData = $config['db'];
$conn = new PDO("mysql:host={$dbData['host']};dbname={$dbData['dbname']}", $dbData['username'], $dbData['password']);

copy(SQL_GENERATOR, SQL_GENERATOR_TARGET);

generateConnection($dbData);

$tables = $conn->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$descs = [];
$creates = [];

foreach ($tables as $table) {
    $descs[$table] = $conn->query("DESC $table")->fetchAll(PDO::FETCH_ASSOC);
    $creates[$table] = $conn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM)[1];
}

$conn = null;

DEFINE('TPL_CONTENT', file_get_contents(TPL_FILE));

generateEntities($descs, $creates);
