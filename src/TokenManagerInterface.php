<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa;

interface TokenManagerInterface
{
    /**
     * Takes a cookie and returns a serialized JWT token.
     */
    public function getSignedToken(Cookie $cookie, ?int $lifetime = null) : string;

    /**
     * Takes a serialized JWT token and returns a cookie instance.
     *
     * If the serialized token is invalid for some reason (invalid data, expired), null will be returned.
     */
    public function parseSignedToken(string $serializedToken) : ?Cookie;
}
