<?php
declare(strict_types=1);

namespace DASPRiD\Pikkuleipa;

use CultuurNet\Clock\Clock;
use CultuurNet\Clock\SystemClock;
use DateTimeZone;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CookieManager implements CookieManagerInterface
{
    /**
     * @var CookieSettings
     */
    private $defaultCookieSettings;

    /**
     * @var CookieSettings[]
     */
    private $cookieSettings = [];

    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;

    /**
     * @var Clock
     */
    private $clock;

    public function __construct(
        CookieSettings $defaultCookieSettings,
        TokenManagerInterface $tokenManager,
        ?Clock $clock = null
    ) {
        $this->defaultCookieSettings = $defaultCookieSettings;
        $this->tokenManager = $tokenManager;
        $this->clock = $clock ?: new SystemClock(new DateTimeZone('UTC'));
    }

    public function withCookieSettings(string $cookieName, CookieSettings $cookieSettings) : self
    {
        $cookieManager = new self($this->defaultCookieSettings, $this->tokenManager, $this->clock);
        $cookieManager->cookieSettings = $this->cookieSettings;
        $cookieManager->cookieSettings[$cookieName] = $cookieSettings;
        return $cookieManager;
    }

    public function getCookie(ServerRequestInterface $request, string $cookieName) : Cookie
    {
        $requestCookie = FigRequestCookies::get($request, $cookieName);
        $cookieValue = $requestCookie->getValue();

        if (null === $cookieValue) {
            return new Cookie($cookieName);
        }

        $cookie = $this->tokenManager->parseSignedToken($cookieValue);

        if (null === $cookie || $cookie->getName() !== $cookieName) {
            return new Cookie($cookieName);
        }

        return $cookie;
    }

    public function setCookie(
        ResponseInterface $response,
        Cookie $cookie,
        bool $overwriteExpireCookie = true
    ) : ResponseInterface {
        $cookieName = $cookie->getName();

        if (! $overwriteExpireCookie && 1 === FigResponseCookies::get($response, $cookieName)->getExpires()) {
            return $response;
        }

        $cookieSettings = $this->cookieSettings[$cookieName] ?? $this->defaultCookieSettings;
        $currentTimestamp = $this->clock->getDateTime()->getTimestamp();
        $setCookie = SetCookie::create($cookieName)
            ->withHttpOnly(true)
            ->withPath($cookieSettings->getPath())
            ->withExpires($cookie->endsWithSession() ? null : $currentTimestamp + $cookieSettings->getLifetime())
            ->withSecure($cookieSettings->isSecure());

        return FigResponseCookies::set(
            $response,
            $setCookie->withValue(
                $this->tokenManager->getSignedToken(
                    $cookie,
                    $cookie->endsWithSession() ? null : $cookieSettings->getLifetime()
                )
            )
        );
    }

    public function expireCookie(ResponseInterface $response, Cookie $cookie) : ResponseInterface
    {
        return $this->expireCookieByName($response, $cookie->getName());
    }

    public function expireCookieByName(ResponseInterface $response, string $cookieName) : ResponseInterface
    {
        $cookieSettings = $this->cookieSettings[$cookieName] ?? $this->defaultCookieSettings;
        $setCookie = SetCookie::create($cookieName)
            ->withHttpOnly(true)
            ->withPath($cookieSettings->getPath())
            ->withExpires(1)
            ->withSecure($cookieSettings->isSecure())
            ->withValue('');

        return FigResponseCookies::set($response, $setCookie);
    }
}
