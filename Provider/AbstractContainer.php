<?php
namespace Provider;

abstract class AbstractContainer implements \ArrayAccess
{
    /**
     * stored service
     * @var array
     */
    protected $_service = [];

    /**
     * get service object
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_service)) {
            return $this->_service[$name];
        }

        $method = $this->getSetterName($name);

        if (method_exists($this, $method)) {
            return $this->_service[$name] = call_user_func([$this, $method]);
        } else {
            throw new \InvalidArgumentException("Not defined service - " . $name);
        }
    }

    /**
     * 세터 메소드 이름 얻기
     * @param  string $name
     * @return string
     */
    private function getSetterName($name)
    {
        return 'set' . ucfirst($name);
    }

    public function offsetExists($offset)
    {
        return method_exists($this, $this->getSetterName($name));
    }

    public function offsetSet($offset, $value)
    {
        $this->_service[$offset] = $value;
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetUnset($offset)
    {
        unset($this->_service[$name]);
    }
}
