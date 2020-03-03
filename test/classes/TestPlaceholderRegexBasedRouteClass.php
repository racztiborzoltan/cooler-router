<?php
declare(strict_types=1);

namespace Test;

use CoolerRouter\RouteInterface;
use CoolerRouter\PlaceholderRegexBasedRouteTrait;

class TestPlaceholderRegexBasedRouteClass implements RouteInterface
{
    use TestRouteClassTrait;
    use PlaceholderRegexBasedRouteTrait;
}
