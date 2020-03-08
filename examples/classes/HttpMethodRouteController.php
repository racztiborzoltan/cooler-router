<?php
declare(strict_types = 1);

namespace Example;

use OtherRouter\RouteInterface;
use OtherRouter\HttpMethodRouteTrait;

class HttpMethodRouteController implements RouteInterface
{

    use ExampleProcessMethodTrait;
    use HttpMethodRouteTrait;
}
