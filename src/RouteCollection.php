<?php
declare(strict_types=1);

namespace CoolerRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class RouteCollection implements RouteInterface
{

    private $_collection_name = null;

    private $_routes = [];

    public function setCollectionName(string $collection_name)
    {
        $this->_collection_name = $collection_name;
        return $this;
    }

    public function getCollectionName(): ?string
    {
        return $this->_collection_name;
    }

    public function setRouteName(string $name): RouteInterface
    {
        return $this->setCollectionName($name);
    }

    public function getRouteName(): ?string
    {
        return $this->getCollectionName();
    }

    public function addRoute(RouteInterface $route)
    {
        if (!in_array($route, $this->_routes)) {
            $route_name = $route->getRouteName();
            if ($route_name) {
                $this->_routes[$route_name] = $route;
            } else {
                $this->_routes[] = $route;
            }
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
            if ($route instanceof RouteCollection) {
                /**
                 * @var RouteCollection $route_collection
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

    public function createRouteUri(ServerRequestInterface $request, array $route_parameters = []): UriInterface
    {
        return $request->getUri();
    }

    public function createUri(ServerRequestInterface $request, string $route_name, array $parameters = []): ?UriInterface
    {
        // This is very important!!!
        $this->isProcessableRoute($request);

        $route = $this->getRoute($route_name);

        if ($route) {
            return $route->createRouteUri($request, $parameters);
        }

        foreach ($this->getRoutes() as $route) {
            if (! ($route instanceof RouteCollection)) {
                continue;
            }
            /**
             * @var RouteCollection $route_collection
             */
            $route_collection = $route;
            $uri = $route_collection->createUri($request, $route_name);
            if ($uri) {
                return $uri;
            }
            unset($route_collection);
        }

        throw new \LogicException('route not found: "'.$route_name.'"');
    }

    public function isProcessableRoute(ServerRequestInterface $request): bool
    {
        foreach ($this->getRoutes() as $route) {
            /**
             * @var RouteInterface $route
             */
            if ($route instanceof RouteCollection) {
                /**
                 * @var RouteCollection $route
                 */
                if ($route->getProcessableRoute($request)) {
                    return true;
                }
            } elseif ($route->isProcessableRoute($request)) {
                return true;
            }
        }

        return false;
    }

    public function getProcessableRoute(ServerRequestInterface $request): ?RouteInterface
    {
        if (!$this->isProcessableRoute($request)) {
            return null;
        }

        /**
         * @var RouteInterface $route
         */
        foreach ($this->getRoutes() as $route) {

            if ($route instanceof RouteCollection) {

                /**
                 * @var RouteCollection $route_collection
                 */
                $route_collection = $route;
                $route_collection_processable_route = $route_collection->getProcessableRoute($request);
                if ($route_collection_processable_route) {
                    return $route_collection_processable_route;
                }
                unset($route_collection);

            } elseif ($route->isProcessableRoute($request)) {

                return $route;

            }
        }
        unset($route);

        return null;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $matched_route = $this->getProcessableRoute($request);

        if ($matched_route) {
            return $matched_route->process($request, $handler);
        }

        return $handler->handle($request);
    }
}
