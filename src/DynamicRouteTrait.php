<?php
declare(strict_types=1);

namespace CoolerRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * trait for dynamic route class definitions
 *
 * This trait class implement the methods of RouteInterface!
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait DynamicRouteTrait
{

    private $_route_name = null;

    private $_route_http_methods = [];

    private $_route_pattern = null;

    private $_route_placeholder_begin = '<<';

    private $_route_placeholder_end = '>>';

    private $_route_placeholder_regex_separator = ':';

    private $_route_placeholder_regex_group_name_prefix = 'placeholder__';

    public function setRouteName(string $route_name): RouteInterface
    {
        $this->_route_name = $route_name;
        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->_route_name;
    }

    public function isProcessable(ServerRequestInterface $request): bool
    {
        //
        // Check http method:
        //
        $http_method = strtoupper($request->getMethod());
        if (!in_array($http_method, $this->getRouteHttpMethods())) {
            return false;
        }

        $regex = $this->convertRoutePatternToRegex();
        $path = $request->getUri()->getPath();
        return (bool)preg_match($regex, $path);
    }

    /**
     * Detect and return the list of placeholder names in route pattern
     *
     * @return array associative array
     */
    public function detectRoutePlaceholderNames(): array
    {
        $matches = [];
        preg_match_all($this->_getRoutePatternPlaceholderRegex(), $this->getRoutePattern(), $matches, PREG_SET_ORDER);
        return array_column($matches, 'placeholder_name');
    }

    /**
     * Detect and return the list of placeholders in route pattern
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    public function detectRoutePlaceholders(ServerRequestInterface $request): array
    {
        $regex = $this->convertRoutePatternToRegex();
        $path = $request->getUri()->getPath();
        $matches = [];
        preg_match($regex, $path, $matches);

        $placeholders = [];
        foreach ($matches as $key => $value) {
            if (is_numeric($key)) {
                continue;
            }
            if (strpos($key, $this->_route_placeholder_regex_group_name_prefix) !== 0) {
                continue;
            }
            $placeholder_name = substr($key, strlen($this->_route_placeholder_regex_group_name_prefix));
            $placeholder_value = $value;
            $placeholders[$placeholder_name] = $placeholder_value;
        }

        return $placeholders;
    }

    public function createRouteUri(ServerRequestInterface $request, array $route_parameters = []): UriInterface
    {
        $placeholder_names = $this->detectRoutePlaceholderNames();
        $placeholder_names = array_combine($placeholder_names, $placeholder_names);

        foreach ($placeholder_names as $placeholder_name) {
            if (!isset($route_parameters[$placeholder_name])) {
                throw new \LogicException('"'.$placeholder_name.'" route parameter value not exists');
            }
        }

        $plus_parameters = [];

        foreach ($route_parameters as $parameter_name => $parameter_value) {
            if (!isset($placeholder_names[$parameter_name])) {
                $plus_parameters[$parameter_name] = $parameter_value;
            }
        }

        $uri_path = preg_replace_callback($this->_getRoutePatternPlaceholderRegex(), function($match) use ($route_parameters) {
            return isset($route_parameters[$match['placeholder_name']]) ? $route_parameters[$match['placeholder_name']] : '';
        }, $this->getRoutePattern());

        $uri = $request->getUri()->withPath($uri_path)->withQuery('');

        if (!empty($plus_parameters)) {
            $uri = $uri->withQuery(http_build_query($plus_parameters));
        }

        return $uri;
    }

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

    public function getRouteHttpMethods(): iterable
    {
        return array_values($this->_route_http_methods);
    }

    public function clearRouteHttpMethods()
    {
        $this->_route_http_methods = [];
        return $this;
    }

    public function setRoutePattern(string $route_pattern)
    {
        $this->_route_pattern = $route_pattern;
        return $this;
    }

    public function getRoutePattern(): ?string
    {
        if (empty($this->_route_pattern)) {
            throw new \LogicException('Router pattern is empty. Use the ->setPattern() method before!');
        }
        return $this->_route_pattern;
    }

    protected function _getRoutePatternPlaceholderRegex(): string
    {
        /*
        $placeholder_regex_pattern = '~'.preg_quote('<?').'(.*?)(:(.*?))?'.preg_quote('?>').'~im';
        */
        // (?P<word>REGEX)
        $pattern_placeholder_regex = '~
            # 1) pattern begin characters
            (?P<pattern_begin>'.preg_quote($this->getRoutePlaceholderBegin()).')
            (?P<placeholder_name>(.*?))
            #
            # 2) part of pattern regex:
            #
            (
                # 2.1) pattern regex separator character
                '.preg_quote($this->getRoutePlaceholderRegexSeparator()).'
                # 2.2) pattern regex
                (?P<placeholder_regex>(.*?))
            )?
            # 3) pattern end characters
            (?P<pattern_end>'.preg_quote($this->getRoutePlaceholderEnd()).')
        ~xim';

        return $pattern_placeholder_regex;
    }

    public function convertRoutePatternToRegex(): string
    {
        $pattern = $this->getRoutePattern();
        if (empty($pattern)) {
            return '';
        }

        return '~^' . preg_replace_callback($this->_getRoutePatternPlaceholderRegex(), function($match){
            if (!isset($match['placeholder_name'])) {
                return $match[0];
            }
            $placeholder_name = $match['placeholder_name'];
            $placeholder_regex = empty($match['placeholder_regex']) ? '.*?' : $match['placeholder_regex'];
            return '(?P<' . $this->_route_placeholder_regex_group_name_prefix . $placeholder_name . '>' . $placeholder_regex . ')';
        }, $pattern) . '$~';
    }

    public function setRoutePlaceholderBegin(string $route_placeholder_begin)
    {
        $this->_route_placeholder_begin = $route_placeholder_begin;
        return $this;
    }

    public function getRoutePlaceholderBegin(): string
    {
        return $this->_route_placeholder_begin;
    }

    public function setRoutePlaceholderEnd(string $route_placeholder_end)
    {
        $this->_route_placeholder_end = $route_placeholder_end;
        return $this;
    }

    public function getRoutePlaceholderEnd(): string
    {
        return $this->_route_placeholder_end;
    }

    public function setRoutePlaceholderRegexSeparator(string $route_placeholder_regex_separator)
    {
        $this->_route_placeholder_regex_separator = $route_placeholder_regex_separator;
        return $this;
    }

    public function getRoutePlaceholderRegexSeparator(): string
    {
        return $this->_route_placeholder_regex_separator;
    }
}
