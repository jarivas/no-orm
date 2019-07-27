<?php


namespace Entity;


trait EntityBody
{
    public function __call($name, $arguments)
    {
        $methodType = substr($name, 0 , 3);

        if(!in_array($methodType, SQLGenerator::ON_CALL_ALLOWED_METHODS))
            throw new Exception('The method do not exists');

        $property = substr($name, 3);

        if (empty(self::$pascalCasedProperties[$property]))
            throw new Exception("The property {$property} you are trying to access do not exists");


        if ($methodType === 'get') return $this->$property;

        $this->$property = $arguments[0];

        return $this;
    }

    public static function getColumnsInfo(): array
    {
        return self::$snakeCasedProperties;
    }

    public static function get(array $projection = [], array $where = [], string $group = '', array $having = [], string $order = '', int $limit = 1000, int $offset = 0) : array
    {
        $joins = [];

        if(!count($projection))
            $projection = SQLGenerator::generateProjection(self::$tableName, self::getColumnsInfo(), $projection, false);

        if(count(self::$relations)) {
            foreach(self::$relations as $relation) {
                $relationProjection = SQLGenerator::generateProjection($relation['tableName'], $relation['columnsInfo']());
                $projection = array_merge($relationProjection, $projection);

                $joins[] = ['type' => 'LEFT', 'tableName' => $relation['tableName'], 'onCol1' => $relation['onCol1'], 'onCol2' => $relation['onCol2']];
            }
        }

        $sql = SQLGenerator::generateSelect(self::$tableName, $projection, $joins, $where, $group, $having, $order, $limit, $offset);

        $connection = Connection::getInstance();

        return $connection->get($sql, __CLASS__);
    }

    public static function insertOne(array $columns, array $values, string $onDuplicateKeyUpdate = '') : int
    {
        $sql = SQLGenerator::generateInsert(self::$tableName, $columns, $values, $onDuplicateKeyUpdate);

        $connection = Connection::getInstance();

        return $connection->executeSql($sql);
    }

    public static function update(array $assignments, array $where = [], string $order = '', int $limit = 0) : bool
    {
        $sql = SQLGenerator::generateUpdate(self::$tableName, $assignments, $where, $order, $limit);

        $connection = Connection::getInstance();

        return ($connection->executeSql($sql) > 0);
    }

    public static function delete(array $where = [], string $order = '', int $limit = 0): bool
    {
        $sql = SQLGenerator::generateDelete(self::$tableName, $where, $order, $limit);

        $connection = Connection::getInstance();

        return ($connection->executeSql($sql) > 0);
    }
}