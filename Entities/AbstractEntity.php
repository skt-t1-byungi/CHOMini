<?php
namespace Entities;

use PDO;
use Provider\EntityFactory;

abstract class AbstractEntity
{
    /**
     * @var Provider\EntityFactory
     */
    protected $_factory;

    /**
     * @var PDO
     */
    protected $_pdo;

    /**
     * attribute of entity
     * @var array
     */
    protected $_data = [];

    /**
     * joined object list
     * @var array
     */
    protected $_joinEntities = [];

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    public function __construct(EntityFactory $factory, PDO $pdo, $tableName)
    {
        $this->_factory = $factory;
        $this->_pdo = $pdo;
        $this->tableName = $tableName;

        if (!empty($this->_data)) {
            $this->setJoin();
        }
    }

    public function __toString()
    {
        return $this->_data[$this->primaryKey];
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_joinEntities)) {
            return $this->_joinEntities[$name];
        } else if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        } else {
            throw new \InvalidArgumentException("not exists field name : " . $name);
        }
    }

    public function __set($name, $val)
    {
        $this->_data[$name] = $val;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * entitiy 속성($this->data)을 db에 업데이트한다.
     * @param  array  $data
     */
    public function save($data = [])
    {
        $this->_data = array_merge($this->_data, $data);
        $query = $this->makeUpdateQuery();
        $this->_pdo->query($query);
        $this->setJoin();
    }

    /**
     * @return string
     */
    protected function makeUpdateQuery()
    {
        $query = 'update ' . $tableName . ' set ';
        foreach ($data as $key => $val) {
            $query .= $this->_pdo->quote($key) . '=' . $this->_pdo->quote($val);
        }
        $query .= ' where ' . $this->primaryKey . '=' . $tdata[$this->primaryKey];

        return $query;
    }

    /**
     * no operation - overide하여 조인설정을 한다.
     * exmaple :
     * protected function setJoin()
     * {
     *     $this->joinOne('category', 'id', 'parent_id');
     *     $this->joinMany('comments', 'parent_id');
     * }
     * @example {}
     */
    protected function setJoin()
    {
        //noop
    }

    /**
     * 단일 join 설정한다.
     * @see self::setJoin
     * @param  string       $joinName
     * @param  string       $joinKey
     * @param  string|null  $hasKey     null일 경우, primarykey에 해당하는 속성값이 default
     */
    protected function joinOne($joinName, $joinKey, $hasKey = null)
    {
        if (array_key_exists($hasKey, $this->_data)) {
            $keyVal = $this->_data[$hasKey];
        } else {
            $keyVal = $this->primaryKey;
        }

        $this->_joinEntities[$joinName] = $this->_factory->findOne($joinName, [$joinKey => $hasKey]);
    }

    /**
     * 복수 join 설정한다.
     * @see self::setJoin
     * @param  string       $joinName
     * @param  string       $joinKey
     * @param  string|null  $hasKey     null일 경우, primarykey에 해당하는 속성값이 default
     */
    protected function joinMany($joinName, $joinKey, $hasKey = null)
    {
        if (array_key_exists($hasKey, $this->_data)) {
            $keyVal = $this->_data[$hasKey];
        } else {
            $keyVal = $this->primaryKey;
        }

        $this->_joinEntities[$joinName] = $this->_factory->find($joinName, [$joinKey => $hasKey]);
    }
}
