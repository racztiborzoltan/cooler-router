<?php
declare(strict_types = 1);

namespace Example;

use OtherRouter\RouteInterface;
use OtherRouter\CompositeRouteTrait;

class CompositeRouteController implements RouteInterface
{

    use ExampleProcessMethodTrait;
    use CompositeRouteTrait;
}
