<?php

declare(strict_types=1);

namespace Test;

use Nyholm\Psr7\Factory\Psr17Factory;
use CoolerRouter\RouteCollection;
use Example\RegexBasedRoute;
use Example\RequestNotFoundRequestHandler;
use Example\SimplestRoute;
use Example\PlaceholderRegexBasedRoute;

$composer = include __DIR__ . '/../vendor/autoload.php';
$composer->addPsr4('Example\\', __DIR__ . '/classes');

header('Content-Type: text/plain');

$route_collection = new RouteCollection();

$simple_route = new SimplestRoute();
$route_collection->addRoute($simple_route);

$regex_route = new RegexBasedRoute();
$regex_route->setRouteName('blog_date_list');
$regex_route->setRouteRegexPattern('/regex-(?<name>\w+)-(?<id>\d+)');
$regex_route->setRouteHttpMethods(['GET', 'post']);
$route_collection->addRoute($regex_route);

PlaceholderRegexBasedRoute::setDefaultRoutePlaceholderRegex('id', '\d+');

$placeholder_regex_route = new PlaceholderRegexBasedRoute();
$placeholder_regex_route->setRoutePattern('/placeholder-test');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id}');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id?}');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id?}/{date}');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id?}/{date?}');
$placeholder_regex_route->setRoutePattern('/placeholder-test-{id?}/{date?}/{slug-text}');
$placeholder_regex_route->setPlaceholderRegex('date', '\d{4}-\d{2}-\d{2}');
$placeholder_regex_route->setPlaceholderRegex('slug-text', '[\w\d-]+');
$route_collection->addRoute($placeholder_regex_route);

$factory = new Psr17Factory();

$test_route_paths = [
    '/page-not-found',
    '/simplest-route-url',
    '/regex-test-122',
    '/placeholder-test',
    '/placeholder-test-123/2020-12-20',
    '/placeholder-test-123/'.date('Y-m-d').'/url-friendly-text',
];

foreach ($test_route_paths as $test_route_path) {
    $request = $factory->createServerRequest('get', $test_route_path);

    $response = $route_collection->process($request, new RequestNotFoundRequestHandler());

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
