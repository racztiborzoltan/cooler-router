<?php

declare(strict_types=1);

namespace Example;

use CoolerRouter\RouteInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\UriInterface;

class SimplestRoute implements RouteInterface
{

    private $_route_name = null;

    public function setRouteName(string $name): RouteInterface
    {
        $this->_route_name = $name;
        return $this;
    }

    public function getRouteName(): ?string
    {
        return 'simple_route_name';
    }

    public function createRouteUri(ServerRequestInterface $request, array $route_parameters = []): UriInterface
    {
        $factory = new Psr17Factory();
        return $factory->createUri('/simplest-route-url');
    }

    public function isProcessableRoute(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        return $path == $this->createRouteUri($request)->getPath();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = (new Psr17Factory())->createResponse();
        $response->getBody()->write('request handled in ' . __METHOD__);
        return $response;
    }
}
