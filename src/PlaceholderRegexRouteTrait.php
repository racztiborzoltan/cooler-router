<?php
declare(strict_types=1);

namespace OtherRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * trait for regular expression based route class definitions
 *
 * This trait class implement the methods of RouteInterface!
 *
 * Syntax of placeholders in route pattern:
 *  - required placeholder: {placeholder_name}
 *  - optional placeholder: {placeholder_name?}
 *
 * The default regex pattern of placeholders: .+
 *
 * Set custom regex of placeholder with ->setRouteplaceholderRegex() method!
 * (The ->getRouteplaceholderRegex() method is exists also.)
 *
 * Set custom default regex of placeholder with ::setDefaultRoutePlaceholderRegex()
 * static method!
 * (The ::getDefaultRoutePlaceholderRegex() static method is exists also.)
 *
 * Get values of placeholder by UriInterface object with the ->getRoutePlaceholderValues()
 * method!
 *
 * The methods to handle enabled http methods:
 *  ->addRouteHttpMethod()
 *  ->removeRouteHttpMethod()
 *  ->setRouteHttpMethods()
 *  ->getRouteHttpMethods()
 *  ->clearRouteHttpMethods()
 *
 * Any other is equivalent to methods of RouteInterface.
 *
 * Note: the base idea of syntax of placeholders from Laravel!
 *
 * @TODO enable-disable the trailing slash character removing!!!
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait PlaceholderRegexRouteTrait
{

    /**
     * @var iterable
     */
    private $_route_http_methods = null;

    public function addRouteHttpMethod(string $http_method)
    {
        $http_method = strtoupper($http_method);
        $this->_route_http_methods[$http_method] = $http_method;
        return $this;
    }

    public function removeRouteHttpMethod(string $http_method)
    {
        $http_method = strtolower($http_method);
        unset($this->_route_http_methods[$http_method]);
        return $this;
    }

    public function setRouteHttpMethods(array $http_methods)
    {
        foreach ($http_methods as $http_method) {
            $this->addRouteHttpMethod($http_method);
        }
        return $this;
    }

    public function getRouteHttpMethods(): ?iterable
    {
        if (!is_iterable($this->_route_http_methods)) {
            return null;
        }

        return is_iterable($this->_route_http_methods) ? array_values((array)$this->_route_http_methods) : null;
    }

    public function clearRouteHttpMethods()
    {
        $this->_route_http_methods = [];
        return $this;
    }

    private $_route_pattern = null;

    public function setRoutePattern(string $route_pattern)
    {
        $this->_route_pattern = $route_pattern;
        return $this;
    }

    public function getRoutePattern(): ?string
    {
        if (empty($this->_route_pattern)) {
            throw new \LogicException('Route pattern is empty. Use the ->setRoutePattern() method before!');
        }
        return $this->_route_pattern;
    }

    private static $_default_route_placeholder_regex = [];

    public static function setDefaultRoutePlaceholderRegex(string $placeholder_name, $placeholder_regex): void
    {
        self::$_default_route_placeholder_regex[$placeholder_name] = $placeholder_regex;
    }

    public static function getDefaultRoutePlaceholderRegex(string $placeholder_name, string $default_value = null): ?string
    {
        return self::$_default_route_placeholder_regex[$placeholder_name] ?? $default_value;
    }

    private $_placeholders = [];

    protected function _getRoutePlaceholderRegexGroupName(string $placeholder_name): string
    {
        return 'regex_group_' . hash("crc32b", $placeholder_name);
    }

    protected function _getRoutePatternRegex(): string
    {
        $route_pattern = $this->getRoutePattern();

        foreach (self::$_default_route_placeholder_regex as $default_placeholder_name => $default_placeholder_regex) {
            $this->_placeholders[$default_placeholder_name]['name'] = $default_placeholder_name;
            $this->_placeholders[$default_placeholder_name]['regex'] = $default_placeholder_regex;
            $this->_placeholders[$default_placeholder_name]['regex_group_name'] = $this->_getRoutePlaceholderRegexGroupName($default_placeholder_name);
        }

        $route_pattern = preg_replace_callback('#\{(?<placeholder_name>.+?)\}#', function($match){

            $placeholder_is_optional = false;

            $placeholder_name = $match['placeholder_name'];
            if ($placeholder_name[strlen($placeholder_name) - 1] == '?') {
                $placeholder_is_optional = true;
                $placeholder_name = substr($placeholder_name, 0, -1);
            }

            if (!isset($this->_placeholders[$placeholder_name])) {
                $this->_placeholders[$placeholder_name] = [];
            }

            $placeholder_regex = '';
            if (isset($this->_placeholders[$placeholder_name]['regex'])) {
                $placeholder_regex = $this->_placeholders[$placeholder_name]['regex'];
            } else {
                $placeholder_regex = '.+?';
            }
            $placeholder_regex = '(' . $placeholder_regex  . ')';

            if ($placeholder_is_optional) {
                $placeholder_regex .= '?';
            }

            $this->_placeholders[$placeholder_name]['name'] = $placeholder_name;
            $this->_placeholders[$placeholder_name]['pattern'] = $match[0];
            $this->_placeholders[$placeholder_name]['optional'] = $placeholder_is_optional;
            $this->_placeholders[$placeholder_name]['regex'] = $placeholder_regex;
            if (!isset($this->_placeholders[$placeholder_name]['regex_group_name'])) {
                $this->_placeholders[$placeholder_name]['regex_group_name'] = $this->_getRoutePlaceholderRegexGroupName($placeholder_name);
            }

            $placeholder_regex_group_name = $this->_placeholders[$placeholder_name]['regex_group_name'];

            return '(?<'.preg_quote($placeholder_regex_group_name).'>'.$placeholder_regex.')';
        }, $route_pattern);

        return '#^'.$route_pattern.'$#';
    }

    public function setRouteplaceholderRegex(string $placeholder_name, string $placeholder_regex): self
    {
        $this->_placeholders[$placeholder_name]['name'] = $placeholder_name;
        $this->_placeholders[$placeholder_name]['regex'] = $placeholder_regex;
        return $this;
    }

    public function getRouteplaceholderRegex(string $placeholder_name, string $default_return_value = null): ?string
    {
        return $this->_placeholders[$placeholder_name]['regex'] ?? $default_return_value;
    }

    public function getRoutePlaceholderValues(UriInterface $uri)
    {
        $path = $uri->getPath();
        $regex = $this->_getRoutePatternRegex();

        $matches = [];
        preg_match_all($regex, $path, $matches, PREG_SET_ORDER);
        if (empty($matches[0])) {
            $matches[0] = [];
        }
        $matches = $matches[0];

        $placeholder_values = [];
        foreach ($this->_placeholders as $placeholder) {
            if (!isset($placeholder['regex_group_name']) || !isset($matches[$placeholder['regex_group_name']])) {
                continue;
            }

            $placeholder_value = $matches[$placeholder['regex_group_name']];

            if ($placeholder['optional'] && $placeholder_value == '') {
                continue;
            }

            $placeholder_values[$placeholder['name']] = $placeholder_value;
        }

        return $placeholder_values;
    }

    public function isProcessable(ServerRequestInterface $request): bool
    {
        //
        // Check http method:
        //
        $http_method = strtoupper($request->getMethod());

        $route_http_methods = $this->getRouteHttpMethods();

        if (is_iterable($route_http_methods) && !in_array($http_method, (array)$route_http_methods)) {
            return false;
        }

        $regex = $this->_getRoutePatternRegex();
        $path = $request->getUri()->getPath();

        return (bool)preg_match($regex, $path);
    }

    public function createRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        // refilling the placeholder informations:
        $this->_getRoutePatternRegex();

        $route_pattern = $this->getRoutePattern();

        $uri = $request->getUri();

        $path = $route_pattern;

        $route_parameters = $request->getAttributes();
        foreach (array_keys($route_parameters) as $route_parameter_name) {
            if (!isset($this->_placeholders[$route_parameter_name])) {
                throw new \LogicException('In this route is not presented the following route parameter: ' . $route_parameter_name);
            }
        }

        foreach ($this->_placeholders as $placeholder) {

            if (!isset($placeholder['pattern'])) {
                continue;
            }

            $placeholder_pattern_matched = preg_match('#'.preg_quote($placeholder['pattern']).'#', $route_pattern);
            if (!$placeholder_pattern_matched) {
                continue;
            }

            // remove the optional placeholder from uri path if not presented in
            // second argument:
            if ($placeholder['optional'] && !isset($route_parameters[$placeholder['name']])) {
                $path = preg_replace('#'.preg_quote($placeholder['pattern']).'#', '', $path);
                continue;
            }

            if (!isset($route_parameters[$placeholder['name']])) {
                throw new \LogicException('This route parameter is required: ' . $placeholder['name']);
            }

            $path = preg_replace('#'.preg_quote($placeholder['pattern']).'#', $route_parameters[$placeholder['name']], $path);
        }

        return $request->withUri($uri->withPath($path));
    }
}
