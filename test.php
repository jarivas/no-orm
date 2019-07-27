<?php

spl_autoload_register(function ($className) {
    $className = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    echo "AUTOLOAD $className" . PHP_EOL;
    include $className;
});

$result = Entity\CurrentDeptEmp::get();

var_dump($result[0]->getEmpNo());
echo PHP_EOL;
var_dump($result[0]);

$result = Entity\DeptEmp::get();
var_dump($result[0]);
die(PHP_EOL);
