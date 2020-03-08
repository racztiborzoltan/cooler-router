<?php

declare(strict_types=1);

namespace Example;

use Psr\Http\Server\RequestHandlerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteNotFoundController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Psr17Factory())->createResponse();
        $response->getBody()->write('REQUEST FAILED - ROUTE NOT FOUND');
        $response = $response->withStatus(404);
        return $response->withHeader('Content-Type', 'text/plain');
    }
}
