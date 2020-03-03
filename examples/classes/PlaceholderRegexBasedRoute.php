<?php

declare(strict_types=1);

namespace Example;

use CoolerRouter\RouteInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use CoolerRouter\PlaceholderRegexBasedRouteTrait;

class PlaceholderRegexBasedRoute implements RouteInterface
{
    use PlaceholderRegexBasedRouteTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = (new Psr17Factory())->createResponse();
        $response->getBody()->write('request handled in ' . __METHOD__);
        return $response;
    }
}
