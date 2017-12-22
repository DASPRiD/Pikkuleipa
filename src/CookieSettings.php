<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa;

final class CookieSettings
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $secure;

    /**
     * @var int
     */
    private $lifetime;

    public function __construct(string $path, bool $secure, int $lifetime)
    {
        $this->path = $path;
        $this->secure = $secure;
        $this->lifetime = $lifetime;
    }

    /**
     * Gets the path for the cookie.
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Returns whether the cookie should be marked as HTTPS only.
     */
    public function isSecure() : bool
    {
        return $this->secure;
    }

    /**
     * Gets the lifetime of the cookie in seconds.
     */
    public function getLifetime() : int
    {
        return $this->lifetime;
    }
}
