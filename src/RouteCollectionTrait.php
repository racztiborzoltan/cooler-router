<?php
declare(strict_types=1);

namespace OtherRouter;

use Psr\Http\Message\ServerRequestInterface;

trait RouteCollectionTrait
{

    private $_routes = [];

    public function addRoute(RouteInterface $route, string $route_name = null)
    {
        if ($route_name) {
            $this->_routes[$route_name] = $route;
        } else {
            $this->_routes[] = $route;
        }
        return $this;
    }

    public function getRoute(string $route_name): ?RouteInterface
    {
        if (isset($this->_routes[$route_name])) {
            return $this->_routes[$route_name];
        }

        foreach ($this->getRoutes() as $route) {
            /**
             * @var RouteInterface $route
             */
            if (static::_isInstanceOfRouteCollectionTrait($route)) {
                /**
                 * @var RouteCollectionTrait $route_collection
                 */
                $route_collection = $route;
                $matched_route = $route_collection->getRoute($route_name);
                if ($matched_route) {
                    return $matched_route;
                }
                unset($route_collection, $matched_route);
            }
        }

        return null;
    }

    public function getRoutes(): iterable
    {
        return $this->_routes;
    }

    public function clearRoutes()
    {
        $this->_routes = [];
        return $this;
    }

    public function isProcessable(ServerRequestInterface $request): bool
    {
        foreach ($this->getRoutes() as $route) {
            /**
             * @var RouteInterface $route
             */
            if ($route->isProcessable($request)) {
                return true;
            }
        }

        return false;
    }

    public function createRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $processable_route = $this->getProcessableRoute($request);

        if ($processable_route) {
            $request = $processable_route->createRequest($request);
        }
        return $request;
    }

    public function getProcessableRoute(ServerRequestInterface $request): ?RouteInterface
    {
        /**
         * @var RouteInterface $route
         */
        foreach ($this->getRoutes() as $route) {
            if ($route->isProcessable($request)) {
                return $route;
            }
        }
        unset($route);

        return null;

        if ($this->isProcessable($request)) {

//             /**
//              * @var RouteInterface $route
//              */
//             foreach ($this->getRoutes() as $route) {
//                 if (static::_isInstanceOfRouteCollectionTrait($route)) {

//                     /**
//                      * @var RouteCollectionTrait $route_collection
//                      */
//                     $route_collection = $route;
//                     $route_collection_processable_route = $route_collection->getProcessableRoute($request);
//                     if ($route_collection_processable_route) {
//                         return $route_collection_processable_route;
//                     }
//                     unset($route_collection);
//                 } elseif ($route->isProcessable($request)) {
//                     return $route;
//                 }
//             }
//             unset($route);

        }

        return null;
    }

    protected static function _isInstanceOfRouteCollectionTrait($object): bool
    {
        return array_key_exists(explode('::', __METHOD__)[0], static::_getTraitList($object));
    }

    /**
     * @see https://www.php.net/manual/en/function.class-uses.php#122427
     *
     * @param object $class
     * @param boolean $autoload
     * @return array
     */
    protected static function _getTraitList($class, $autoload = true)
    {
        $traits = [];

        // Get all the traits of $class and its parent classes
        do {
            $class_name = is_object($class)? get_class($class): $class;
            if (class_exists($class_name, $autoload)) {
                $traits = array_merge(class_uses($class, $autoload), $traits);
            }
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traits_to_search = $traits;
        while (!empty($traits_to_search)) {
            $new_traits = class_uses(array_pop($traits_to_search), $autoload);
            $traits = array_merge($new_traits, $traits);
            $traits_to_search = array_merge($new_traits, $traits_to_search);
        };

        return array_unique($traits);
    }
}
