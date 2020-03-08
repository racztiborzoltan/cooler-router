<?php
declare(strict_types=1);

namespace Example;

use OtherRouter\RouteInterface;
use OtherRouter\RouteCollectionTrait;

class RouteCollection implements RouteInterface
{
    use RouteCollectionTrait;
}