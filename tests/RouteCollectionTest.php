<?php
declare(strict_types=1);

namespace Test;

use PHPUnit\Framework\TestCase;
use OtherRouter\StaticUriRouteTrait;
use OtherRouter\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

final class StaticUriRouteTest extends TestCase
{

    /**
     * @return StaticUriRouteTrait
     */
    protected function _getRoute(): StaticUriRoute
    {
        return new StaticUriRoute();
    }

    protected function _getRouteNotFound(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = (new Psr17Factory())->createResponse();
                $response->getBody()->write('ROUTE NOT FOUND');
                return $response->withStatus(404);
            }
        };
    }

    public function test_with_processable_uri(): void
    {
        $http_factory = new Psr17Factory();

        $static_uri = $http_factory->createUri('/static-url');

        $request = $http_factory->createServerRequest('get', $static_uri);

        $route = $this->_getRoute();
        $route->setStaticUri($static_uri);

        $this->assertTrue($route->isProcessable($request));
        $this->assertEquals($static_uri, $route->createRequest($http_factory->createServerRequest('get', '/'))->getUri());
    }

    public function test_with_not_processable_uri(): void
    {
        $http_factory = new Psr17Factory();

        $static_uri = $http_factory->createUri('/static-url');

        $request = $http_factory->createServerRequest('get', '/not-processable-uri');

        $route = $this->_getRoute();
        $route->setStaticUri($static_uri);

        $response = $route->process($request, $this->_getRouteNotFound());

        $this->assertFalse($route->isProcessable($request));
        $this->assertEquals($static_uri, $route->createRequest($http_factory->createServerRequest('get', '/'))->getUri());
    }
}

class StaticUriRoute implements RouteInterface {

    use StaticUriRouteTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = (new Psr17Factory())->createResponse();
        $response->getBody()->write(get_called_class());
        return $response;
    }
}
