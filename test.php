<?php

use Entity\CurrentDeptEmp;

spl_autoload_register(function ($className) {
    include str_replace('\\', '/', $className) . '.php';
});

$result = CurrentDeptEmp::get();

var_dump($result[0]->getEmpNo());
echo PHP_EOL;
var_dump($result[0]);
die(PHP_EOL);
