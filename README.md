# no-orm
A simple and light SQL lib that you were needing it and not knowing

- Generates Entities
- Requires very basic Mysql knowledge
- No obscure code
- Pascal cased getter and setters
- Column names as constants
- Handles relationships automatically

## Getting Started
- `composer require jarivas/no-orm`
- $host is the mysql host
- $dbname schema name where read tables to generate helper classes
- $username mysql user
- $password mysql password
- $targetFolder where in the file system you want the files to be located
- $namespace, is a string with a standard php namespace, for example 'Db\\Book'

### Generating classes
- ```use NoOrm\EntityGenerator;```
- ```$result = EntityGenerator::process($host, $dbname, $username, $password, $targetFolder, $namespace)```
- My recommendation is create php command in order to generate files everytime you want

### Examples
- I have been using this db example https://github.com/datacharmer/test_db
- Select the first 1000 rows `$result = Entity\CurrentDeptEmp::get();`
- Get the value of the column emp_no of the first row `$result[0]->getEmpNo()`
- Insert
```
$columns = [
    Entity\Departments::COL_DEPT_NO,
    Entity\Departments::COL_DEPT_NAME
];
   
$values = [
    'd010',
    'Test'
];
   
$result = Entity\Departments::insertOne($columns, $values);
```
- Update
```
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
```
- Delete `$result = Entity\Departments::delete($where);`

## API
- All the generated classes contains `const`s that begins with `COL` that represent that column in the db, example `CurrentDeptEmp::COL_DEPT_NO` 
- The where and having syntax are the same across the methods
- Projection means the selected fields from the query
- If you do not specify any projection by default will generate all fields PascalCased instead of snake_cased `emp_no` => `EmpNo`, also will include the ones on the relations
- The relations fields will have the upper case table name prefixed `DEPARTMENTS_DeptNo` or `EMPLOYEES_BirthDate`
- The trait EntityBody will be on each generated class and will be the one who generates and executes the queries
- Those methods are `get`, `insertOne`, `update`, `delete`

```
/**
 * Retrieves an array of Entities (same class where was invoked)
 * @param array $projection (array of class's columns constant)
 * @param array $where (array of ['operator' => AND|OR, 'condition' => COL_CONSTANT . ' =, like ....'])
 * @param string $group (an string that you would place after GROUP BY)
 * @param array $having (same as $where)
 * @param string $order (an string that you would place after ORDER BY)
 * @param int $limit (in case you need it)
 * @param int $offset (in case you need it)
 * @return array[Entity]
 */
public static function get(array $projection = [], array $where = [],
    string $group = '', array $having = [], string $order = '',
    int $limit = 1000, int $offset = 0): array


/**
 * Inserts a new row in the table
 * @param array $columns (array of class's columns constant)
 * @param array $values (array of values in the same order as $columns)
 * @param string $onDuplicateKeyUpdate (an string that you would place after ON DUPLICATED KEY UPDATE)
 * @return int (number of rows affected)
 */
public static function insertOne(array $columns, array $values,
        string $onDuplicateKeyUpdate = ''): int

/**
 * Updates one or more rows in the table
 * @param array $assignments
 * @param array $where (array of ['operator' => AND|OR, 'condition' => COL_CONSTANT . ' =, like ....'])
 * @param string $order (an string that you would place after ORDER BY)
 * @param int $limit (in case you need it)
 * @return bool (true in case of success)
 */
public static function update(array $assignments, array $where = [],
    string $order = '', int $limit = 0): bool

/**
 * Deletes one or more rows in the table
 * @param array $where (array of ['operator' => AND|OR, 'condition' => COL_CONSTANT . ' =, like ....'])
 * @param string $order (an string that you would place after ORDER BY)
 * @param int $limit (in case you need it)
 * @return bool (true in case of success)
 */
public static function delete(array $where = [], string $order = '', int $limit = 0): bool

```
