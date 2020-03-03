<?php
declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Test\TestPlaceholderRegexBasedRouteClass;
use Psr\Http\Message\ServerRequestInterface;

final class PlaceholderRegexBasedRouteTest extends TestCase
{

    public function test_no_placeholder(): void
    {
        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts');
        $route_parameters = [];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/posts';
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts');
        $route_parameters = [];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts');
        $expected_processable = true;
        $expected_route_url = '/blog/posts';
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));
    }

    public function test_one_placeholder(): void
    {
        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/post/{id}');
        $route_parameters = [
            'id' => 123,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/post/' . $route_parameters['id'];
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/post/{id}');
        $route_parameters = [
            'id' => 123,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/post/' . $route_parameters['id']);
        $expected_processable = true;
        $expected_route_url = '/blog/post/' . $route_parameters['id'];
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));
    }

    public function test_placeholder_one_custom_placeholder(): void
    {
        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/post/{id}');
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [
            'id' => 123,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/post/' . $route_parameters['id'];
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/post/{id}');
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [
            'id' => 123,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/post/' . $route_parameters['id'];
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));
    }

    public function test_placeholder_one_optional_placeholder(): void
    {
        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{page?}');
        // add not used placeholder regex:
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{page?}');
        // add not used placeholder regex:
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/');
        $expected_processable = true;
        $expected_route_url = '/blog/posts/';
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{page?}');
        // add not used placeholder regex:
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/' . implode('/', $route_parameters));
        $expected_processable = true;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));
    }

    public function test_placeholder_two_placeholder(): void
    {
        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        // add not used placeholder regex:
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        // add not used placeholder regex:
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/' . implode('/', $route_parameters));
        $expected_processable = true;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        // add not used placeholder regex:
        $route->setRouteplaceholderRegex('id', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/' . implode('/', $route_parameters) . '/');
        $expected_processable = true;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters) . '/';
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));
    }

    public function test_placeholder_two_custom_placeholder(): void
    {
        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        $route->setRouteplaceholderRegex('date', '\d{4}-\d{2}-\d{2}');
        $route->setRouteplaceholderRegex('page', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        $route->setRouteplaceholderRegex('date', '\d{4}-\d{2}-\d{2}');
        $route->setRouteplaceholderRegex('page', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/' . implode('/', $route_parameters));
        $expected_processable = true;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        $route->setRouteplaceholderRegex('date', '\d{4}-\d{2}-\d{2}');
        $route->setRouteplaceholderRegex('page', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/' . implode('/', $route_parameters) . '/');
        $expected_processable = true;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters) . '/';
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));
    }

    public function test_placeholder_two_custom_default_placeholder(): void
    {
        TestPlaceholderRegexBasedRouteClass::setDefaultRoutePlaceholderRegex('date', '\d{4}-\d{2}-\d{2}');

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        $route->setRouteplaceholderRegex('page', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/not-processable');
        $expected_processable = false;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = [];
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        $route->setRouteplaceholderRegex('page', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
            'page' => 2,
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/' . implode('/', $route_parameters));
        $expected_processable = true;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters);
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));

        $route = new TestPlaceholderRegexBasedRouteClass();
        $route->setRoutePattern('/blog/posts/{date}/{page?}');
        $route->setRouteplaceholderRegex('page', '\d+');
        $route_parameters = [
            'date' => date('Y-m-d'),
        ];
        $request = (new Psr17Factory())->createServerRequest('GET', '/blog/posts/' . implode('/', $route_parameters) . '/');
        $expected_processable = true;
        $expected_route_url = '/blog/posts/' . implode('/', $route_parameters) . '/';
        $expected_placeholder_values = $route_parameters;
        $this->assertEquals($expected_processable, $route->isProcessableRoute($request));
        $this->assertEquals($expected_route_url, (string)$route->createRouteUri($request, $route_parameters));
        $this->assertEquals($expected_placeholder_values, $route->getRoutePlaceholderValues($request->getUri()));
    }
}
