<?php
declare(strict_types=1);

namespace Test;

use CoolerRouter\RouteInterface;
use CoolerRouter\RegexBasedRouteTrait;

class TestRegexBasedRouteClass implements RouteInterface
{
    use TestRouteClassTrait;
    use RegexBasedRouteTrait;
}
