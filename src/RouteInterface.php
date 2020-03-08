<?php
declare(strict_types=1);

namespace OtherRouter;

use Psr\Http\Message\ServerRequestInterface;

interface RouteInterface
{

    /**
     * Is this route processable?
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function isProcessable(ServerRequestInterface $request): bool;

    /**
     * Create ServerRequestInterface object based on other ServerRequestInterface object
     * which matches this route
     *
     * @param ServerRequestInterface $request
     * @param array $route_parameters
     * @return ServerRequestInterface
     */
    public function createRequest(ServerRequestInterface $request): ServerRequestInterface;
}
