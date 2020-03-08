<?php
declare(strict_types=1);

namespace OtherRouter;

use Psr\Http\Message\ServerRequestInterface;

/**
 * trait for http method based route class definitions
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait HttpMethodRouteTrait
{

    /**
     * @var iterable
     */
    private $_http_methods = null;

    public function addHttpMethod(string $http_method)
    {
        $http_method = strtoupper($http_method);
        $this->_http_methods[$http_method] = $http_method;
        return $this;
    }

    public function removeHttpMethod(string $http_method)
    {
        $http_method = strtolower($http_method);
        unset($this->_http_methods[$http_method]);
        return $this;
    }

    public function setHttpMethods(array $http_methods)
    {
        foreach ($http_methods as $http_method) {
            $this->addHttpMethod($http_method);
        }
        return $this;
    }

    public function getHttpMethods(): array
    {
        return array_values((array)$this->_http_methods);
    }

    public function clearHttpMethods()
    {
        $this->_http_methods = [];
        return $this;
    }

    public function isProcessable(ServerRequestInterface $request): bool
    {
        //
        // Check http method:
        //
        $http_method = strtoupper($request->getMethod());

        $http_methods = $this->getHttpMethods();

        if (is_iterable($http_methods) && !in_array($http_method, (array)$http_methods)) {
            return false;
        }

        return true;
    }

    public function createRequest(ServerRequestInterface $request, array $parameters = []): ServerRequestInterface
    {
        $http_methods = $this->getHttpMethods();
        return $request->withMethod(reset($http_methods));
    }
}
