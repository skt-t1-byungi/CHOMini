<?php
namespace Provider;

abstract class AbstractContainer
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

        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->_service[$name] = call_user_func([$this, $method]);
        } else {
            throw new \DomainException("not defined service.");
        }
    }
}
