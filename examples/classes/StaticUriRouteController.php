<?php
declare(strict_types = 1);

namespace Example;

use OtherRouter\RouteInterface;
use OtherRouter\StaticUriRouteTrait;

class StaticUriRouteController implements RouteInterface
{

    use ExampleProcessMethodTrait;
    use StaticUriRouteTrait;
}
