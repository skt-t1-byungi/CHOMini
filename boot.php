<?php
define('BASEDIR', __DIR__);

require_once 'autoload.php';

$container = new Provider\Container;
$router = $container->router;
require_once 'route.php';

$router->run();
