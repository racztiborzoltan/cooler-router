<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use CoolerRouter\RouteCollection;
use CoolerRouter\RouteInterface;
use CoolerRouter\DynamicRouteTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

final class CoolerRouterTest extends TestCase
{

    /**
     *
     * @return Psr17Factory
     */
    public static function createHttpFactory(): Psr17Factory
    {
        return new Psr17Factory();
    }

    protected function getRequestHandler(): RequestHandlerInterface
    {
        return new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = CoolerRouterTest::createHttpFactory()->createResponse();
                $response->getBody()->write('REQUEST FAILED - ROUTE NOF FOUND');
                $response = $response->withStatus(404);
                $response = $response->withHeader('Content-Type', 'text/plain');
                return $response;
            }
        };
    }

    public function getRouter(): RouteCollection
    {
        $router = new RouteCollection();

        //
        // route for main page
        //
        $index_route = new TestRouteClass();
        $index_route->setRouteName('index_page');
        $index_route->setRoutePattern('/');
        $index_route->setTestContent('INDEX ROUTE CONTENT');
        $index_route->setRouteHttpMethods(['get', 'POST']);
        $router->addRoute($index_route);

        //
        // route for contact page
        //
        $contact_page_route = new TestRouteClass();
        $contact_page_route->setRouteName('contact_page');
        $contact_page_route->setRoutePattern('/contact');
        $contact_page_route->setTestContent('CONTACT ROUTE CONTENT');
        $contact_page_route->setRouteHttpMethods(['get']);
        $router->addRoute($contact_page_route);

        //
        // route group for blog pages
        //
        $router->addRoute(new class extends RouteCollection {

            public function getCollectionName(): ?string
            {
                return 'blog_route_group';
            }

            public function isProcessable(ServerRequestInterface $request): bool
            {
                $group_path_pattern_prefix = '/blog';

                $group_is_processable = strpos($request->getUri()->getPath(), $group_path_pattern_prefix) === 0;

                //
                // Bejelentkezés képernyő
                //
                $blog_list_route = new TestRouteClass();
                $blog_list_route->setRouteName('blog_index');
                $blog_list_route->setTestContent('BLOG LIST ROUTE CONTENT');
                $blog_list_route->setRoutePattern($group_path_pattern_prefix . '-list');
                $blog_list_route->setRouteHttpMethods(['get']);
                $this->addRoute($blog_list_route);

                //
                // Dinamikus url
                //
                $test_route = new TestRouteClass();
                $test_route->setRouteName('blog_date_list');
                $test_route->setRoutePattern('/blog-<<year>>-<<month:\d\d>>-<<day:\d{2}>>');
                $test_route->setTestContent('BLOG DATE LIST ROUTE CONTENT');
                $test_route->setRouteHttpMethods(['GET', 'post']);
                $this->addRoute($test_route);

                return $group_is_processable;
            }
        });

        return $router;
    }

    public function test_simple(): void
    {
        $http_factory = static::createHttpFactory();
        $request = $http_factory->createServerRequest('GET', '/');

        $router = $this->getRouter();

        $this->assertTrue($router->isProcessable($request));
        $response = $router->process($request, $this->getRequestHandler());
        $this->assertEquals($router->getRoute('index_page')->getTestContent(), $response->getHeader('Test-Content')[0]);

        // change request uri to contact page:
        $request = $request->withUri($request->getUri()->withPath('/contact'));
        $this->assertTrue($router->isProcessable($request));
        $response = $router->process($request, $this->getRequestHandler());
        $this->assertEquals($router->getRoute('contact_page')->getTestContent(), $response->getHeader('Test-Content')[0]);

        // change request uri to not existed page:
        $request = $request->withUri($request->getUri()->withPath('/sure-not-exists'));
        $this->assertFalse($router->isProcessable($request));
        $response = $router->process($request, $this->getRequestHandler());
        $this->assertEquals('404', $response->getStatusCode());

        // change request uri to index page and POST method:
        $request = $request->withUri($request->getUri()->withPath('/'))->withMethod('POST');
        $this->assertTrue($router->isProcessable($request));

        // change request uri to contact page and POST method:
        $request = $request->withUri($request->getUri()->withPath('/contact'))->withMethod('POST');
        $this->assertFalse($router->isProcessable($request));

        // change request uri back to GET method:
        $request = $request->withMethod('GET');

        $request = $request->withUri($request->getUri()->withPath('/blog-asdg'))->withMethod('GET');
        $this->assertFalse($router->isProcessable($request));

        $request = $request->withUri($request->getUri()->withPath('/blog'))->withMethod('GET');
        $this->assertFalse($router->isProcessable($request));

        $request = $request->withUri($request->getUri()->withPath('/blog-list'))->withMethod('GET');
        $this->assertTrue($router->isProcessable($request));
        $response = $router->process($request, $this->getRequestHandler());
        $this->assertEquals($router->getRoute('blog_index')->getTestContent(), $response->getHeader('Test-Content')[0]);

        $request = $request->withUri($request->getUri()->withPath('/blog-' . date('Y-m-d')))->withMethod('GET');
        $this->assertTrue($router->isProcessable($request));
        $response = $router->process($request, $this->getRequestHandler());
        $this->assertEquals($router->getRoute('blog_date_list')->getTestContent(), $response->getHeader('Test-Content')[0]);

        $request = $request->withUri($request->getUri()->withPath('/blog-' . date('Y-m-d')))->withMethod('post');
        $this->assertTrue($router->isProcessable($request));
        $response = $router->process($request, $this->getRequestHandler());
        $this->assertEquals($router->getRoute('blog_date_list')->getTestContent(), $response->getHeader('Test-Content')[0]);
    }
}


class TestRouteClass implements RouteInterface
{

    use DynamicRouteTrait;

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

        $response = CoolerRouterTest::createHttpFactory()->createResponse();
        $response->getBody()->write('TEST CONTROLLER - ' . __METHOD__ . ' - REQUEST ATTRBIUTES: ' . var_export($request->getAttributes(), true));
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response = $response->withHeader('Route-Placeholders', json_encode($route_placeholders));
        $response = $response->withHeader('Test-Content', $this->getTestContent());
        return $response;
    }
}
