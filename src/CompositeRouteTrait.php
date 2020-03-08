<?php
declare(strict_types=1);

namespace OtherRouter;

use Psr\Http\Message\ServerRequestInterface;

/**
 * trait for route class definitions that contains different routes
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait CompositeRouteTrait
{

    private $_routes = [];

    public function addRoute(RouteInterface $route)
    {
        $this->_routes[] = $route;
        return $this;
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
        $routes = $this->getRoutes();
        $processable = count($routes) > 0;
        foreach ($routes as $route) {
            /**
             * @var RouteInterface $route
             */
            if (!$route->isProcessable($request)) {
                return false;
            }
        }
        return $processable;
    }

    public function createRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        foreach ($this->getRoutes() as $route) {
            /**
             * @var RouteInterface $route
             */
            $request = $route->createRequest($request);
        }
        return $request;
    }
}
