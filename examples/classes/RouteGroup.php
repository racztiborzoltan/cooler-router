<?php
declare(strict_types = 1);
namespace Example;

use OtherRouter\RouteInterface;
use OtherRouter\RouteGroupTrait;

abstract class RouteGroup implements RouteInterface
{
    use RouteGroupTrait;
}