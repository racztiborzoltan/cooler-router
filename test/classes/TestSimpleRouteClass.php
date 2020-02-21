<?php
declare(strict_types=1);

namespace Test;

use CoolerRouter\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class TestSimpleRouteClass implements RouteInterface
{

    private $_route_name = null;

    public function setRouteName(string $route_name): RouteInterface
    {
        $this->_route_name = $route_name;
        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->_route_name;
    }

    public function isProcessableRoute(ServerRequestInterface $request): bool
    {
        return ltrim($request->getUri()->getPath(), '/') === ltrim($this->getRoutePath(), '/');
    }

    public function createRouteUri(ServerRequestInterface $request, array $route_parameters = []): UriInterface
    {
        $uri = $request->getUri();
        $uri = $uri->withPath($this->getRoutePath());
        $get_params = array_merge($request->getQueryParams(), $route_parameters);
        $uri = $uri->withQuery(http_build_query($get_params));
        return $uri;
    }

    private $_route_path = null;

    public function setRoutePath(string $route_path)
    {
        $this->_route_path = $route_path;
        return $this;
    }

    public function getRoutePath(): string
    {
        return $this->_route_path;
    }

    private $_test_content = null;

    public function setTestContent(string $test_content)
    {
        $this->_test_content = $test_content;
        return $this;
    }

    public function getTestContent(): string
    {
        return $this->_test_content;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route_placeholders = $this->detectRoutePlaceholders($request);
        foreach ($route_placeholders as $placeholder_name => $placeholder_value) {
            $request = $request->withAttribute($placeholder_name, $placeholder_value);
        }

        $response = \CoolerRouterTest::createHttpFactory()->createResponse();
        $response->getBody()->write('TEST CONTROLLER - ' . __METHOD__ . ' - REQUEST ATTRBIUTES: ' . var_export($request->getAttributes(), true));
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response = $response->withHeader('Route-Placeholders', json_encode($route_placeholders));
        $response = $response->withHeader('Test-Content', $this->getTestContent());
        return $response;
    }
}