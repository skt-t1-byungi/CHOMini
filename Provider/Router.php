<?php
namespace Provider;

/**
 * @link http://upshots.org/php/php-seriously-simple-router
 */

use Closure;
use Controller\BaseController;
use Provider\Container;

class Router
{
    const METHOD_DELIMITER = '@';

    /**
     * @var Provider\Container
     */
    protected $container;

    /**
     * 등록된 route
     * @var array
     */
    protected $routes = [];

    /**
     * @see stringToCallable()
     * @var string
     */
    protected $prefix = '';

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @see $prefix
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * route 등록
     * @param string $pattern
     * @param mixed $callback
     */
    public function add($pattern, $callback)
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        $this->routes[$pattern] = $callback;
    }

    /**
     * route 실행
     * @return mixed
     */
    public function run()
    {
        $url = $_SERVER['REQUEST_URI'];

        foreach ($this->routes as $pattern => $callback) {

            if (preg_match($pattern, $url, $params)) {
                array_shift($params);

                //문자열일 경우 해석해서 callable로 변환
                if (is_string($callback)) {
                    $callback = $this->stringToCallable($callback);
                }

                //container 삽입
                $callback = $this->withContainer($callback);

                return call_user_func_array($callback, array_values($params));
            }
        }
    }

    /**
     * 문자를 해석해 callable로 반환한다
     * @example stringToCallable("\Controller\Test@index") => [new \Controller\Test(), 'index']
     * @param  string $str
     * @return callable
     */
    protected function stringToCallable($str)
    {
        $segments = explode(static::METHOD_DELIMITER, $str);

        $method = array_pop($segments);
        $class = $this->prefix . implode('\\', $segments);

        return [new $class($this->container), $method];
    }

    /**
     * 콜백 또는 컨트롤러에 container 삽입한다.
     * @param  callable $callback
     * @return callable
     */
    protected function withContainer(callable $callback)
    {
        if (is_array($callback)) {
            //array type
            $callback[0]->setContainer($this->container);

        } else if ($callback instanceof Closure) {
            //클로져일 경우..
            $controller = new BaseController($this->container);
            $controller->setContainer($this->container);

            $callback = $callback->bindTo($controller, BaseController::class);
        }

        return $callback;
    }
}
