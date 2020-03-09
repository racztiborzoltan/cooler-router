<?php
declare(strict_types = 1);
namespace Example;

use OtherRouter\RouteInterface;
use OtherRouter\RouteCollectionTrait;
use Psr\Http\Message\ServerRequestInterface;

abstract class RouteGroup implements RouteInterface
{
    use RouteCollectionTrait {
        isProcessable as _trait_isProcessable;
        createRequest as _trait_createRequest;
        getProcessableRoute as _trait_getProcessableRoute;
    }

    public abstract function upRequest(ServerRequestInterface $request): ServerRequestInterface;

    public abstract function downRequest(ServerRequestInterface $request): ServerRequestInterface;

    public function isProcessable(ServerRequestInterface $request): bool
    {
        $request = $this->upRequest($request);
        return $this->_trait_isProcessable($request);
    }

    public function createRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $request = $this->upRequest($request);

        $request = $this->_trait_createRequest($request);

        $request = $this->downRequest($request);
        return $request;
    }

    public function getProcessableRoute(ServerRequestInterface $request): ?RouteInterface
    {
        $request = $this->upRequest($request);
        return $this->_trait_getProcessableRoute($request);
    }
}