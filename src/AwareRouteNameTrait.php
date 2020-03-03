<?php
declare(strict_types=1);

namespace CoolerRouter;

/**
 * trait for setting and getting the name of route
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait AwareRouteNameTrait
{
    private $_route_name = null;

    public function setRouteName(string $route_name): RouteInterface
    {
        $this->_route_name = $route_name;
        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->_route_name;
    }
}
