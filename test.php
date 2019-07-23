<?php

use Entity\CurrentDeptEmp;

spl_autoload_register(function ($className) {
    include str_replace('\\', '/', $className) . '.php';
});

$result = CurrentDeptEmp::getAll();

var_dump($result);
die(PHP_EOL);
