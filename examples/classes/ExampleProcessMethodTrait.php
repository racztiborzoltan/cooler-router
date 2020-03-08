<?php
declare(strict_types = 1);

namespace Example;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

trait ExampleProcessMethodTrait
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = (new Psr17Factory())->createResponse();
        $response->getBody()->write('request handled in ' . get_called_class() . '::' . __FUNCTION__);
        return $response;
    }
}
