<?php
declare(strict_types=1);

use Example\RouteCollection;
use Example\StaticUriRouteController;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Example\RouteGroup;
use Example\PlaceholderRegexRouteController;
use OtherRouter\RouteInterface;

$composer = include __DIR__ . '/../vendor/autoload.php';
$composer->addPsr4('Example\\', __DIR__ . '/classes');

header('Content-Type: text/text');

$http_factory = new Psr17Factory();

$route_collection = new RouteCollection();

// -----------------------------------------------
// example route group 1:
//
$route_group = new class extends RouteGroup {

    private $_url_prefix = '/hu';

    public function upRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri_prefix = $this->_url_prefix;
        if (strpos($request->getUri()->getPath(), $uri_prefix) === 0) {
            // Remove the url prefix from request uri:
            $uri = $request->getUri();
            $uri = $uri->withPath(substr($uri->getPath(), strlen($uri_prefix)));
            $request = $request->withUri($uri);
        }
        return $request;
    }

    public function downRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri_prefix = $this->_url_prefix;
        $uri = $request->getUri();
        $uri = $uri->withPath($uri_prefix . $uri->getPath());
        return $request->withUri($uri);
    }
};
// $route_group = new RouteGroup();

$static_route = new StaticUriRouteController();
$static_route->setStaticUri($http_factory->createUri('/blog/list'));
$route_group->addRoute($static_route);

$placeholder_regex_route = new PlaceholderRegexRouteController();
$placeholder_regex_route->setRoutePattern('/blog/list/{page}');
$placeholder_regex_route->setRouteplaceholderRegex('page', '[0-9]+');
$route_group->addRoute($placeholder_regex_route);

$route_collection->addRoute($route_group, 'route_group_1');
unset($route_group);
// -----------------------------------------------

// -----------------------------------------------
// example route group 2:
//
$route_group = new class extends RouteGroup {

    private $_url_postfix = '.html';

    public function upRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri_postfix = $this->_url_postfix;
        if (strpos(strrev($request->getUri()->getPath()), strrev($uri_postfix)) === 0) {
            // Remove the url postfix from request uri:
            $uri = $request->getUri();
            $uri = $uri->withPath(substr($uri->getPath(), 0, -1 * strlen($uri_postfix)));
            $request = $request->withUri($uri);
        }
        return $request;
    }

    public function downRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $uri_postfix = $this->_url_postfix;
        $uri = $request->getUri();
        $uri = $uri->withPath($uri->getPath() . $uri_postfix);
        return $request->withUri($uri);
    }
};

$static_route = new StaticUriRouteController();
$static_route->setStaticUri($http_factory->createUri('/foobar'));
$route_group->addRoute($static_route);

$route_collection->addRoute($route_group, 'route_group_2');
unset($route_group);
// -----------------------------------------------

$example_list = [
    $http_factory->createServerRequest('get', '/hu/blog/list'),
    [
        'request' => $http_factory->createServerRequest('get', '/hu/blog/list/5'),
        'route_parameters' => [
            'page' => 10
        ],
    ],
    $http_factory->createServerRequest('get', '/foobar.html'),
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

        foreach ($route_parameters as $route_parameter_name => $route_parameter_value) {
            $example_request = $example_request->withAttribute($route_parameter_name, $route_parameter_value);
        }
        unset($route_parameter_name, $route_parameter_value);

        echo PHP_EOL;
        echo 'CREATED URL FROM PROCESSABLE ROUTE:' . $processable_route->createRequest($example_request, $route_parameters)->getUri();
        echo PHP_EOL;

        echo PHP_EOL;
        echo 'CREATED URL FROM ROUTE COLLECTION: ' . $route_collection->createRequest($example_request, $route_parameters)->getUri();
        echo PHP_EOL;

        foreach ($route_collection->getRoutes() as $route_collection_route) {
            if ($route_collection_route instanceof RouteGroup) {
                /**
                 * @var RouteGroup $route_group_in_collection
                 */
                $route_group_in_collection = $route_collection_route;
                foreach ($route_group_in_collection->getRoutes() as $route_group_route) {
                    /**
                     * @var RouteInterface $route_group_route
                     */
                    if ($processable_route == $route_group_route) {
                        echo PHP_EOL;
                        echo 'CREATED URL FROM ROUTE GROUP: ' . $route_group_in_collection->createRequest($example_request, $route_parameters)->getUri();
                        echo PHP_EOL;
                    }
                }
                unset($route_group_in_collection, $route_group_route);
            }
            unset($route_collection_route);
        }

    }
    echo str_repeat('=', 80);
    echo PHP_EOL;
    echo PHP_EOL;

    unset($example_request, $route_parameters);
}
