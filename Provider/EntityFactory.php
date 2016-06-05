<?php
namespace Provider;

use PDO;

class EntityFactory
{

    /**
     * 바인딩될 entity class namespace
     * @var string
     */
    const ENTITY_PREFIX = '\Entities';

    /**
     * table name prefix
     * @var string
     */
    const TABLE_PREFIX = '';

    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * $where에 해당하는 entity들을 찾아서 배열로 반환한다.
     * @param  string $name
     * @param  array  $where
     * @return object[]
     */
    public function find($name, $where = [], $prepend = '')
    {
        list($tableName, $className) = $this->getNamesOfTableAndEntity($name);

        $query = $this->makeSelectQuery($tableName, $where) . ' ' . $prepend;
        $result = $this->pdo->query($query);
        $result->setFetchMode(PDO::FETCH_CLASS, $className, [$this, $this->pdo, $tableName]);

        return $result->fetchAll();
    }

    /**
     * $where에 해당하는 entity 찾아서 반환한다.
     * @param  string $name
     * @param  array  $where
     * @return object
     */
    public function findOne($name, $where = [])
    {
        list($tableName, $className) = $this->getNamesOfTableAndEntity($name);

        $query = $this->makeSelectQuery($tableName, $where) . ' limit 1';
        $result = $this->pdo->query($query);
        $result->setFetchMode(PDO::FETCH_CLASS, $className, [$this, $this->pdo, $tableName]);

        return $result->fetch();
    }

    /**
     * entity을 생성하고 반환한다.
     * @param  string $name
     * @param  array  $data
     * @return object
     */
    public function create($name, $data = [])
    {
        list($tableName, $className) = $this->getNamesOfTableAndEntity($name);

        //insert
        $query = $this->makeInsertQuery($tableName, $data);
        $this->pdo->query($query);
        $id = $this->pdo->lastInsertId();

        //create tmpEntity obj
        $entity = new $className($this, $this->pdo, $tableName);
        $primaryKey = $entity->getPrimaryKey();

        //data bind to tmpEntity obj
        $query = $this->makeSelectQuery($tableName, [$primaryKey => $id]) . ' limit 1';
        $result = $this->pdo->query($query);
        $result->setFetchMode(PDO::FETCH_INTO, $entity);
        $result->fetch();

        return $entity;
    }

    /**
     * @param  string $name
     * @return string[]
     */
    protected function getNamesOfTableAndEntity($name)
    {
        return [
            static::TABLE_PREFIX . $name,
            static::ENTITY_PREFIX . '\\' . $name,
        ];
    }

    /**
     * @param  string $tableName
     * @param  array  $where
     * @return string
     */
    protected function makeSelectQuery($tableName, $where = [])
    {
        $query = 'select * from ' . $tableName . ' where 1=1';
        foreach ($where as $key => $val) {
            $query .= ' and ' . $key . '=' . $this->pdo->quote($val);
        }

        return $query;
    }

    /**
     * @param  string $tableName
     * @param  array  $data
     * @return string
     */
    protected function makeInsertQuery($tableName, $data = [])
    {
        $query = 'insert into ' . $tableName;
        $query .= ' (' . implode(',', array_keys($data)) . ')';
        $query .= ' values (' . implode(',', array_map([$this->pdo, 'quote'], $data)) . ')';

        return $query;
    }
}
