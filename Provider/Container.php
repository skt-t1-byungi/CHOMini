<?php
namespace Provider;

use PDO;
use Provider\EntityFactory;
use Provider\Router;
use Provider\Template;

class Container extends AbstractContainer
{
    /**
     * @return League\Plates\Engine
     */
    protected function setView()
    {
        //템플릿 주소
        $tempaltesDir = BASEDIR . '/templates';
        return new Template($tempaltesDir);
    }

    /**
     * @return Provider\Router
     */
    protected function setRouter()
    {
        return new Router($this);
    }

    /**
     * @return PDO
     */
    protected function setPdo()
    {
        $pdo = new PDO("mysql:host=localhost;dbname=test", 'test', '1234');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        return $pdo;
    }

    /**
     * @return Provider\Entity\Factory
     */
    protected function setEntityFactory()
    {
        return new EntityFactory($this->pdo);
    }

}
