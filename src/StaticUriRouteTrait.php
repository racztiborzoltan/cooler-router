<?php
declare(strict_types=1);

namespace OtherRouter;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * trait for static uri based route class definitions
 *
 * @author Rácz Tibor Zoltán <racztiborzoltan@gmail.com>
 *
 */
trait StaticUriRouteTrait
{

    /**
     * @var UriInterface
     */
    private $_static_uri = null;

    public function setStaticUri(UriInterface $static_uri)
    {
        $this->_static_uri = $static_uri;
        return $this;
    }

    public function getStaticUri(): UriInterface
    {
        if (empty($this->_static_uri)) {
            throw new \LogicException('static uri is empty. Please use before the ->setStaticUri() method!');
        }
        return $this->_static_uri;
    }

    public function isProcessable(ServerRequestInterface $request): bool
    {
        return (string)$this->getStaticUri() == (string)$request->getUri();
    }

    public function createRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withUri($this->getStaticUri());
    }
}
