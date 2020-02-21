<?php
declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Test\TestSimpleRouteClass;
use Test\TestRegexBasedRouteClass;

final class CreateRouteUriTest extends AbstractTest
{

    /**
     *
     * @return Psr17Factory
     */
    public static function createHttpFactory(): Psr17Factory
    {
        return new Psr17Factory();
    }

    public function test_simple(): void
    {
        $static_url = '/simple-static-route';

        $simple_route = new TestSimpleRouteClass();
        $simple_route->setRoutePath($static_url);

        $request = \CoolerRouterTest::createHttpFactory()->createServerRequest('GET', '/simple-static-route');

        $this->assertEquals($static_url, (string)$simple_route->createRouteUri($request));
    }

    public function test_regex_based(): void
    {
        $placeholders = [
            'year' => date('Y'),
            'month' => date('m'),
            'day' => date('d'),
        ];

        $expected = '/blog-list/' . implode('-', $placeholders);

        $test_route = new TestRegexBasedRouteClass();
        $test_route->setRouteRegexPattern('/blog-list/(?<year>\d+)-(?<month>\d+)-(?<day>\d+)');
        $test_route->setRouteHttpMethods(['get']);

        $request = \CoolerRouterTest::createHttpFactory()->createServerRequest('GET', '/');

        $test_route_uri = $test_route->createRouteUri($request, $placeholders);

        $this->assertEquals($expected, (string)$test_route_uri);
    }

    public function test_regex_based_2(): void
    {
        $placeholders = [
            'path' => 'PATH',
            'sub_path' => 'SUB_PATH',
            'sub_sub_path' => 'SUB_SUB_PATH',
        ];

        $expected = '/' . implode('/', $placeholders);

        $test_route = new TestRegexBasedRouteClass();
        $test_route->setRouteRegexPattern('/(?<path>[A-Za-z]+)/(?<sub_path>\w+)/(?<sub_sub_path>\w+)');
        $test_route->setRouteHttpMethods(['get']);

        $request = \CoolerRouterTest::createHttpFactory()->createServerRequest('GET', '/');

        $test_route_uri = $test_route->createRouteUri($request, $placeholders);
        $this->assertEquals($expected, (string)$test_route_uri);
    }
}
