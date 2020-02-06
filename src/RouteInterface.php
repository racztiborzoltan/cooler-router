<?php
declare(strict_types=1);

namespace CoolerRouter;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

interface RouteInterface extends MiddlewareInterface
{

    /**
     * Set name of route
     *
     * @param string $name
     * @return self
     */
    public function setRouteName(string $name): RouteInterface;

    /**
     * Return the name of route
     *
     * @return string|NULL
     */
    public function getRouteName(): ?string;

    /**
     * Is this route processable?
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function isProcessable(ServerRequestInterface $request): bool;

    /**
     * Create UriInterface object from route
     *
     * @param ServerRequestInterface $request
     * @param array $route_parameters
     * @return UriInterface
     */
    public function createRouteUri(ServerRequestInterface $request, array $route_parameters = []): UriInterface;
}
