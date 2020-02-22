<?php

declare(strict_types=1);

namespace Test;

use Nyholm\Psr7\Factory\Psr17Factory;
use CoolerRouter\RouteCollection;
use Example\RegexBasedRoute;
use Example\RequestNotFoundRequestHandler;
use Example\SimplestRoute;

$composer = include __DIR__ . '/../vendor/autoload.php';
$composer->addPsr4('Example\\', __DIR__ . '/classes');

header('Content-Type: text/plain');

$simple_route = new SimplestRoute();
$dynamic_route = new RegexBasedRoute();
$dynamic_route->setRouteName('blog_date_list');
$dynamic_route->setRouteRegexPattern('/dynamic-(?<name>\w+)-(?<id>\d+)');
$dynamic_route->setRouteHttpMethods(['GET', 'post']);

$collection = new RouteCollection();
$collection->addRoute($simple_route);
$collection->addRoute($dynamic_route);

$factory = new Psr17Factory();

$test_route_paths = [
    '/page-not-found',
    '/simplest-route-url',
    '/dynamic-test-122',
];

foreach ($test_route_paths as $test_route_path) {
    $request = $factory->createServerRequest('get', $test_route_path);

    $response = $collection->process($request, new RequestNotFoundRequestHandler());

    echo str_repeat('=', 80);
    echo PHP_EOL;
    echo PHP_EOL;
    echo 'URL: ' . $request->getUri();
    echo PHP_EOL;
    echo PHP_EOL;
    echo 'RESPONSE: ';
    echo PHP_EOL;
    echo $response->getBody();
    echo PHP_EOL;
    echo str_repeat('=', 80);
    echo PHP_EOL;
    echo PHP_EOL;
}
