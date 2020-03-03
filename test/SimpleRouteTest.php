<?php
declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Test\TestSimpleRouteClass;

final class SimpleRouteTest extends TestCase
{

    public function test_simple_route(): void
    {
        $expected_simple_route_url = '/simple-route-url';

        $route = new TestSimpleRouteClass();
        $route->setRoutePath('/simple-route-url');
        $this->assertEquals($expected_simple_route_url, $route->getRoutePath());

        $http_factory = new Psr17Factory();

        $request = $http_factory->createServerRequest('GET', '/');
        $this->assertFalse($route->isProcessableRoute($request));
        $this->assertEquals($expected_simple_route_url, $route->createRouteUri($request));

        $request = $http_factory->createServerRequest('GET', $expected_simple_route_url);
        $this->assertTrue($route->isProcessableRoute($request));
        $this->assertEquals($expected_simple_route_url, $route->createRouteUri($request));
    }
}
