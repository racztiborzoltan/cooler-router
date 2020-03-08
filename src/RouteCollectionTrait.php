<?php
declare(strict_types=1);

namespace OtherRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

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
            if ($route instanceof RouteCollectionTrait) {
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

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $matched_route = $this->getProcessableRoute($request);

        if ($matched_route) {
            return $matched_route->process($request, $handler);
        }

        return $handler->handle($request);
    }

    public function getProcessableRoute(ServerRequestInterface $request): ?RouteInterface
    {
        if (!$this->isProcessable($request)) {
            return null;
        }

        /**
         * @var RouteInterface $route
         */
        foreach ($this->getRoutes() as $route) {
            if ($route instanceof RouteCollectionTrait) {

                /**
                 * @var RouteCollectionTrait $route_collection
                 */
                $route_collection = $route;
                $route_collection_processable_route = $route_collection->getProcessableRoute($request);
                if ($route_collection_processable_route) {
                    return $route_collection_processable_route;
                }
                unset($route_collection);
            } elseif ($route->isProcessable($request)) {
                return $route;
            }
        }
        unset($route);

        return null;
    }
}