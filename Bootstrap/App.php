<?php
/**
 *APP 核心类
 *
 * Created At 11/04/2018 6:21 PM.
 * User: kaiyanh
 */

namespace Bootstrap;

class App
{
    /**
     *
     * Instances of the derived classes.
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Get instance of the derived class.
     *
     * @return \Service\BaseService
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className($className);
        }

        return self::$instances[$className];
    }

    /**
     * @param array $routersConfig
     */
    public function run(&$routersConfig)
    {
        $dispatcher = \FastRoute\cachedDispatcher(function (\FastRoute\RouteCollector $r) use ($routersConfig) {
            foreach ($routersConfig as $group => $child) {
                if ($group == 'un_group') {
                    foreach ($child as $item) {
                        $r->addRoute($item[0], $item[1], $item[2]);
                    }
                } else {
                    $r->addGroup('/'.$group, function (\FastRoute\RouteCollector $rChild) use ($child) {
                        if (!empty($child) && is_array($child)) {
                            foreach ($child as $item) {
                                $rChild->addRoute($item[0], $item[1], $item[2]);
                            }
                        }
                    });
                }
            }
        }, [
            'cacheFile'     => BASE_ROOT.'/temp/caches/route.cache', /* required */
            'cacheDisabled' => ENV == "prod" ? false : true,     /* 测试环境缓存不生效 */
        ]);

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        try {
            switch ($routeInfo[0]) {
                case \FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 Not Found
                    break;
                case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    // ... 405 Method Not Allowed
                    break;
                case \FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    list($class, $method) = explode('@', $handler);
                    $class = '\Handler\\'.$class;
                    $obj = new $class();
                    $obj->getProxy()->__call($method, $vars);
                    break;
            }
        } catch (\Exception $e) {
            throw new \Core\Exception($e);
        }

    }
}