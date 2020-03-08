<?php

declare(strict_types=1);

namespace Example;

use OtherRouter\RouteInterface;
use OtherRouter\PlaceholderRegexRouteTrait;

class PlaceholderRegexRouteController implements RouteInterface
{
    use PlaceholderRegexRouteTrait;
    use ExampleProcessMethodTrait;
}
