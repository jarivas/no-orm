<?php

spl_autoload_register(function ($className) {
    $className = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    echo "AUTOLOAD $className" . PHP_EOL;
    include $className;
});

echo 'SELECT TEST' . PHP_EOL;

$result = Entity\CurrentDeptEmp::get();

var_dump($result[0]->getEmpNo());
echo PHP_EOL;
print_r($result[0]);

$result = Entity\DeptEmp::get();
print_r($result[0]);

echo 'INSERT TEST' . PHP_EOL;

$columns = [
    Entity\Departments::COL_DEPT_NO,
    Entity\Departments::COL_DEPT_NAME
];

$values = [
    'd010',
    'Test'
];

$result = Entity\Departments::insertOne($columns, $values);

var_dump($result);

echo 'UPDATE TEST' . PHP_EOL;

$assignments = [
    Entity\Departments::COL_DEPT_NAME . ' = "HOLA"'
];

$where = [
    [
        'operator' => 'AND',
        'condition' => Entity\Departments::COL_DEPT_NO . ' = "d010"'
    ]
];

$result = Entity\Departments::update($assignments, $where);

var_dump($result);

echo 'DELETE TEST' . PHP_EOL;

$result = Entity\Departments::delete($where);

var_dump($result);

die(PHP_EOL);
