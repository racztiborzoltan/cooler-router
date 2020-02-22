<?php
declare(strict_types=1);

namespace Test;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

trait TestRouteClassTrait
{
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
        if ($this instanceof TestRegexBasedRouteClass) {
            $route_placeholders = $this->getRoutePlaceholderValues($request);
            foreach ($route_placeholders as $placeholder_name => $placeholder_value) {
                $request = $request->withAttribute($placeholder_name, $placeholder_value);
            }
        }

        $response = \CoolerRouterTest::createHttpFactory()->createResponse();
        $response->getBody()->write('TEST CONTROLLER - ' . __METHOD__ . ' - REQUEST ATTRBIUTES: ' . var_export($request->getAttributes(), true));
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response = $response->withHeader('Route-Placeholders', json_encode($route_placeholders));
        $response = $response->withHeader('Test-Content', $this->getTestContent());
        return $response;
    }
}
