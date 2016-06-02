<?php

namespace Controller;

use Provider\Container;

class Base
{
    /**
     * @var Provider\Container
     */
    protected $container;

    /**
     * set Provider\Container
     * @param Provider\Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}
