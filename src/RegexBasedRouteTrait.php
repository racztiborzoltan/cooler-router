<?php
declare(strict_types=1);

namespace CoolerRouter;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * trait for regular expression based route class definitions
 *
 * This trait class implement the methods of RouteInterface!
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait RegexBasedRouteTrait
{

    private $_route_name = null;

    private $_route_http_methods = [];

    private $_route_regex_pattern = null;

    private $_route_regex_pattern_prefix = '#^';

    private $_route_regex_pattern_postfix = '$#';

    public function setRouteName(string $route_name): RouteInterface
    {
        $this->_route_name = $route_name;
        return $this;
    }

    public function getRouteName(): ?string
    {
        return $this->_route_name;
    }

    public function isProcessableRoute(ServerRequestInterface $request): bool
    {
        //
        // Check http method:
        //
        $http_method = strtoupper($request->getMethod());

        if (!in_array($http_method, $this->getRouteHttpMethods())) {
            return false;
        }

        $regex = $this->getRouteRegexPattern(true);
        $path = $request->getUri()->getPath();

        return (bool)preg_match($regex, $path);
    }

    public function getRouteRegexMatches(ServerRequestInterface $request)
    {
        $matches = [];
        preg_match($this->getRouteRegexPattern(), $request->getUri()->getPath(), $matches);
        return $matches;
    }

    private function _createPlaceholderRegexPattern(string $placeholder_name_regex, string $placeholder_regex, $preg_quote = false)
    {
        if ($preg_quote) {
            $placeholder_name_regex = preg_quote($placeholder_name_regex);
            $placeholder_regex = preg_quote($placeholder_regex);
        }

        // (?<name>regex)
        // (?P<name>regex)
        // (?'name'regex)
        return '\(\?P?[\<\'](?<placeholder_name>'.$placeholder_name_regex.')?[\>\'](?<placeholder_regex>'.$placeholder_regex.')\)';
    }

    private function _getRouteRegexPlaceholderInformations(UriInterface $uri = null)
    {
        $route_regex_pattern = $this->getRouteRegexPattern();
        $route_regex_pattern_full = $this->getRouteRegexPattern(true);

        // (?<name>regex)
        // (?P<name>regex)
        // (?'name'regex)
        $placeholder_info = [];
        preg_match_all(
            '#'.$this->_createPlaceholderRegexPattern('\w+', '.*?').'#mx', 
            $route_regex_pattern, 
            $placeholder_info,
            PREG_SET_ORDER
        );

        $return = [];
        foreach($placeholder_info as $info) {
            $return[$info['placeholder_name']] = [
                'name' => $info['placeholder_name'],
                'regex' => $info['placeholder_regex'],
                'match' => null,
            ];
        }

        if ($uri) {
            $matches = [];
            preg_match_all($route_regex_pattern_full, $uri->getPath(), $matches, PREG_SET_ORDER);
            if (isset($matches[0])) {
                $matches = $matches[0];
            } else {
                $matches = [];
            }
            foreach($return as &$temp) {
                if (isset($matches[$temp['name']])) {
                    $temp['match'] = $matches[$temp['name']];
                }
            }
            unset($temp);
        }

        return $return;
    }

    /**
     * Return the list of placeholder names in route pattern
     *
     * @return array associative array
     */
    public function getRoutePlaceholderNames(): array
    {
        $placeholder_info = $this->_getRouteRegexPlaceholderInformations();
        return array_keys($placeholder_info);
    }

    /**
     * Return the list of placeholder values in route pattern
     *
     * @return array associative array
     */
    public function getRoutePlaceholderValues(ServerRequestInterface $request): array
    {
        $placeholder_info = $this->_getRouteRegexPlaceholderInformations($request->getUri());
        $placeholder_values = [];
        foreach ($placeholder_info as $info) {
            $placeholder_values[$info['name']] = $info['match'];
        }
        return $placeholder_values;
    }

    /**
     * Return the list of placeholder names in route pattern
     *
     * @return array associative array
     */
    public function getRoutePlaceholderMatches(ServerRequestInterface $request): array
    {
        $placeholder_info = $this->_getRouteRegexPlaceholderInformations($request->getUri());
        $matches = [];
        foreach ($placeholder_info as $info) {
            $matches[$info['name']] = $info['match'];
        }
        return $matches;
    }

    public function createRouteUri(ServerRequestInterface $request, array $route_parameters = []): UriInterface
    {
        $url = $this->getRouteRegexPattern();

        $placeholder_informations = $this->_getRouteRegexPlaceholderInformations();

        $preg_replace_patterns = [];
        $preg_replacements = [];
        
        foreach ($placeholder_informations as $placeholder_information) {

            if (!array_key_exists($placeholder_information['name'], $route_parameters)) {
                continue;
            }
            
            $preg_replace_patterns[] = '#'.$this->_createPlaceholderRegexPattern($placeholder_information['name'], $placeholder_information['regex'], true).'#mx';
            $preg_replacements[] = $route_parameters[$placeholder_information['name']];
        }
        
        $preg_replace_patterns[] = '#^'.preg_quote($this->getRouteRegexPatternPrefix()).'#mx';
        $preg_replacements[] = '';

        $preg_replace_patterns[] = '#'.preg_quote($this->getRouteRegexPatternPostfix()).'$#mx';
        $preg_replacements[] = '';
        
        $url = preg_replace(
            $preg_replace_patterns,
            $preg_replacements,
            $url
        );

        $uri = $request->getUri();

        return $uri->withPath($url);
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

    public function setRouteRegexPattern(string $route_regex_pattern)
    {
        $this->_route_regex_pattern = $route_regex_pattern;
        return $this;
    }

    public function getRouteRegexPattern(bool $full_regex = false): ?string
    {
        if (empty($this->_route_regex_pattern)) {
            throw new \LogicException('Route regex pattern is empty. Use the ->setRouteRegexPattern() method before!');
        }
        if ($full_regex) {
            return $this->getRouteRegexPatternPrefix() . $this->_route_regex_pattern . $this->getRouteRegexPatternPostfix();
        } else {
            return $this->_route_regex_pattern;
        }
    }

    public function setRouteRegexPatternPrefix(string $route_regex_pattern_prefix)
    {
        $this->_route_regex_pattern_prefix = $route_regex_pattern_prefix;
        return $this;
    }

    public function getRouteRegexPatternPrefix(): ?string
    {
        if (empty($this->_route_regex_pattern_prefix)) {
            throw new \LogicException('Route regex pattern prefix is empty. Use the ->setRouteRegexPatternPrefix() method before!');
        }
        return $this->_route_regex_pattern_prefix;
    }

    public function setRouteRegexPatternPostfix(string $route_regex_pattern_postfix)
    {
        $this->_route_regex_pattern_postfix = $route_regex_pattern_postfix;
        return $this;
    }

    public function getRouteRegexPatternPostfix(): ?string
    {
        if (empty($this->_route_regex_pattern_postfix)) {
            throw new \LogicException('Route regex pattern prefix is empty. Use the ->setRouteRegexPatternPostfix() method before!');
        }
        return $this->_route_regex_pattern_postfix;
    }
}
