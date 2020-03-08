<?php
declare(strict_types=1);

use Example\RouteCollection;
use Example\StaticUriRouteController;
use Nyholm\Psr7\Factory\Psr17Factory;
use Example\RouteNotFoundController;
use Example\PlaceholderRegexRouteController;
use Psr\Http\Message\ServerRequestInterface;
use Example\HttpMethodRouteController;
use Example\CompositeRouteController;

$composer = include __DIR__ . '/../vendor/autoload.php';
$composer->addPsr4('Example\\', __DIR__ . '/classes');

header('Content-Type: text/plain');

$http_factory = new Psr17Factory();

$route_collection = new RouteCollection();

// -----------------------------------------------
// example route with static uri:
//
$static_route = new StaticUriRouteController();
$static_route->setStaticUri($http_factory->createUri('/static-route-url'));
$route_collection->addRoute($static_route);
// -----------------------------------------------

// -----------------------------------------------
// example route with http method:
//
$http_method_route = new HttpMethodRouteController();
$http_method_route->addHttpMethod('put');
$http_method_route->addHttpMethod('DELETE');
$route_collection->addRoute($http_method_route);
// -----------------------------------------------

// -----------------------------------------------
// example route with regex placeholders:
//
// PlaceholderRegexRouteController::setDefaultRoutePlaceholderRegex('id', '\d+');

$placeholder_regex_route = new PlaceholderRegexRouteController();
$placeholder_regex_route->setRoutePattern('/placeholder-test');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id}');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id?}');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id?}/{date}');
// $placeholder_regex_route->setRoutePattern('/placeholder-test-{id?}/{date?}');
$placeholder_regex_route->setRoutePattern('/placeholder-test-{id}/{date?}/{slug-text?}');
$placeholder_regex_route->setRouteplaceholderRegex('date', '\d{4}-\d{2}-\d{2}');
$placeholder_regex_route->setRouteplaceholderRegex('slug-text', '[\w\d-]+');
$route_collection->addRoute($placeholder_regex_route);
// -----------------------------------------------

// -----------------------------------------------
// example composite route:
$composite_route = new CompositeRouteController();
$composite_route->addRoute((new StaticUriRouteController())->setStaticUri($http_factory->createUri('/composite-route-example')));
$composite_route->addRoute((new HttpMethodRouteController())->addHttpMethod('patch'));
$route_collection->addRoute($composite_route);
// -----------------------------------------------


$example_list = [
    $http_factory->createServerRequest('get', '/route-not-found'),
    $http_factory->createServerRequest('get', '/static-route-url'),
    $http_factory->createServerRequest('pUT', '/put-http-method-route-example'),
    $http_factory->createServerRequest('DeleTE', '/delete-http-method-route-example'),
    [
        'request' => $http_factory->createServerRequest('get', '/placeholder-test-123/'.date('Y-m-d').'/url-friendly-text'),
        'route_parameters' => ['id' => 9876, 'date' => date('Y-m-d'), 'slug-text' => 'seo-slug-text'],
    ],
    [
        'request' => $http_factory->createServerRequest('patch', '/composite-route-example'),
    ],
];

foreach ($example_list as $example) {

    /**
     * @var ServerRequestInterface $example_request
     */
    if ($example instanceof ServerRequestInterface) {
        $example_request = $example;
        $route_parameters = [];
    }
    if (is_array($example)) {
        if (isset($example['request']) && $example['request'] instanceof ServerRequestInterface) {
            $example_request = $example['request'];
        }
        if (isset($example['route_parameters']) && is_array($example['route_parameters'])) {
            $route_parameters = $example['route_parameters'];
        }
    }
    if (!isset($route_parameters)) {
        $route_parameters = [];
    }

    $processable_route = $route_collection->getProcessableRoute($example_request);

    echo str_repeat('=', 80);
    echo PHP_EOL;
    echo PHP_EOL;
    echo 'URL: ' . $example_request->getUri();
    echo PHP_EOL;
    echo PHP_EOL;
    echo 'IS_PROCESSABLE FROM ROUTE COLLECTION: ' . ($route_collection->isProcessable($example_request) ? 'TRUE' : 'FALSE');
    echo PHP_EOL;
    echo PHP_EOL;
    if ($processable_route) {
        echo 'PROCESSABLE ROUTE CLASS NAME: ';
        echo get_class($processable_route);
        echo PHP_EOL;
    }
    foreach ($route_parameters as $route_parameter_name => $route_parameter_value) {
        $example_request = $example_request->withAttribute($route_parameter_name, $route_parameter_value);
    }
    unset($route_parameter_name, $route_parameter_value);
    if ($processable_route) {
        echo PHP_EOL;
        echo 'CREATED URL:' . $processable_route->createRequest($example_request, $route_parameters)->getUri();
        echo PHP_EOL;
    }
    if ($processable_route) {
        echo PHP_EOL;
        echo 'CREATED URL FROM ROUTE COLLECTION: ' . $route_collection->createRequest($example_request, $route_parameters)->getUri();
        echo PHP_EOL;
    }
    echo str_repeat('=', 80);
    echo PHP_EOL;
    echo PHP_EOL;

    unset($example_request, $route_parameters);
}
