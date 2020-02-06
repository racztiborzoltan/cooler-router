<?php
declare(strict_types=1);

namespace Test;

use CoolerRouter\RouteInterface;
use CoolerRouter\DynamicRouteTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\UriInterface;
use CoolerRouter\RouteCollection;

include 'bootstrap.php';


class SimpleRouteClass implements RouteInterface
{

    private $_route_name = null;

    private $_url_path = '/simple-route-url';

    public function setRouteName(string $name): RouteInterface
    {
        $this->_route_name = $name;
        return $this;
    }

    public function getRouteName(): ?string
    {
        return 'simple_route_name';
    }

    public function isProcessable(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        return $path == $this->_url_path;
    }

    public function createRouteUri(ServerRequestInterface $request, array $route_parameters = []): UriInterface
    {
        $factory = new Psr17Factory();
        return $factory->createUri($this->_url_path);
    }

    public function process(ServerRequestInterface$request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = (new Psr17Factory())->createResponse();
        $response->getBody()->write('request handled in :' . __METHOD__);
        return $response;
    }
}

class DynamicRouteClass implements RouteInterface
{

    use DynamicRouteTrait;

    public function process(ServerRequestInterface$request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = (new Psr17Factory())->createResponse();
        $response->getBody()->write(__METHOD__);
        return $response;
    }
}

$simple_route = new SimpleRouteClass();
$dynamic_route = new DynamicRouteClass();
$dynamic_route->setRoutePattern('dynamic-router');
$dynamic_route->setRouteName('blog_date_list');
$dynamic_route->setRoutePattern('/dynamic-<<name>>-<<id:\d+>>');
$dynamic_route->setRouteHttpMethods(['GET', 'post']);

$collection = new RouteCollection();
$collection->addRoute($simple_route);
$collection->addRoute($dynamic_route);

$factory = new Psr17Factory();
$request = $factory->createServerRequest('get', '/simple-route-url-df');
$response = $collection->process(
    $request,
    new class implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface
        {
            $response = (new Psr17Factory())->createResponse();
            $response->getBody()->write('REQUEST FAILED - ROUTE NOF FOUND');
            $response = $response->withStatus(404);
            $response = $response->withHeader('Content-Type', 'text/plain');
            return $response;
        }
    }
);

echo $response->getBody();
