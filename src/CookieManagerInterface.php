<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CookieManagerInterface
{
    /**
     * Get the specified cookie from the request.
     *
     * If the cookie does not exist in the request or is invalid (expired, invalid data), an empty cookie instance is
     * returned. While you can assume that the data you receive where not tampered with, you should still validate data
     * types if possible.
     */
    public function getCookie(ServerRequestInterface $request, string $cookieName) : Cookie;

    /**
     * Injects a cookie into a response and returns the modified response object.
     *
     * You can define whether the cookie should end with browser session, although the token in the cookie may expire
     * earlier if a lifetime has been defined. By default, this method will overwrite an expire cookie which has been
     * set before, except if your forbid this.
     */
    public function setCookie(
        ResponseInterface $response,
        Cookie $cookie,
        bool $endWithSession = false,
        bool $overwriteExpireCookie = true
    ) : ResponseInterface;

    /**
     * Expires a cookie and returns the modified response.
     */
    public function expireCookie(ResponseInterface $response, Cookie $cookie) : ResponseInterface;

    /**
     * Expires a cookie by its name and returns the modified response.
     */
    public function expireCookieByName(ResponseInterface $response, string $cookieName) : ResponseInterface;
}
