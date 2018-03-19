<?php
declare(strict_types = 1);

namespace DASPRiD\PikkuleipaTest;

use CultuurNet\Clock\FrozenClock;
use DASPRiD\Pikkuleipa\Cookie;
use DASPRiD\Pikkuleipa\CookieManager;
use DASPRiD\Pikkuleipa\CookieSettings;
use DASPRiD\Pikkuleipa\TokenManagerInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;

class CookieManagerTest extends TestCase
{
    public function testSetSecureCookie()
    {
        $cookie = new Cookie('foo');
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->getSignedToken($cookie, 100)->willReturn('bar');
        $cookieManager = $this->createCookieManager($tokenManager->reveal());

        $originalResponse = new EmptyResponse();
        $newResponse = $cookieManager->setCookie(
            $originalResponse,
            $cookie
        );

        $this->assertSame([
            'foo=bar; Path=/foo; Expires=Thu, 01 Jan 1970 00:03:20 GMT; Secure; HttpOnly',
        ], $newResponse->getHeader('Set-Cookie'));
    }

    public function testSetNonSecureCookie()
    {
        $cookie = new Cookie('foo');
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->getSignedToken($cookie, 100)->willReturn('bar');
        $cookieManager = $this->createCookieManager($tokenManager->reveal(), false);

        $originalResponse = new EmptyResponse();
        $newResponse = $cookieManager->setCookie(
            $originalResponse,
            $cookie
        );

        $this->assertSame([
            'foo=bar; Path=/foo; Expires=Thu, 01 Jan 1970 00:03:20 GMT; HttpOnly',
        ], $newResponse->getHeader('Set-Cookie'));
    }

    public function testSetCookieExpiringEndOfSession()
    {
        $cookie = new Cookie('foo', true);
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->getSignedToken($cookie, null)->willReturn('bar');
        $cookieManager = $this->createCookieManager($tokenManager->reveal(), false);

        $originalResponse = new EmptyResponse();
        $newResponse = $cookieManager->setCookie(
            $originalResponse,
            $cookie
        );

        $this->assertSame([
            'foo=bar; Path=/foo; HttpOnly',
        ], $newResponse->getHeader('Set-Cookie'));
    }

    public function testExpireCookieIsNotOverwrittenWithSetFlag()
    {
        $cookie = new Cookie('foo');
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->getSignedToken($cookie, 100)->willReturn('bar');
        $cookieManager = $this->createCookieManager($tokenManager->reveal(), false);

        $originalResponse = new EmptyResponse();
        $expireResponse = $cookieManager->expireCookie($originalResponse, $cookie);
        $newResponse = $cookieManager->setCookie(
            $expireResponse,
            $cookie,
            false
        );

        $this->assertSame([
            'foo=; Path=/foo; Expires=Thu, 01 Jan 1970 00:00:01 GMT; HttpOnly',
        ], $newResponse->getHeader('Set-Cookie'));
    }

    public function testSecureExpireCookie()
    {
        $cookie = new Cookie('foo');
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->getSignedToken($cookie, 100)->willReturn('bar');
        $cookieManager = $this->createCookieManager($tokenManager->reveal(), true);

        $originalResponse = new EmptyResponse();
        $newResponse = $cookieManager->expireCookie($originalResponse, $cookie);

        $this->assertSame([
            'foo=; Path=/foo; Expires=Thu, 01 Jan 1970 00:00:01 GMT; Secure; HttpOnly',
        ], $newResponse->getHeader('Set-Cookie'));
    }

    public function testNonSecureExpireTokenCookie()
    {
        $cookie = new Cookie('foo');
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->getSignedToken($cookie, 100)->willReturn('bar');
        $cookieManager = $this->createCookieManager($tokenManager->reveal(), false);

        $originalResponse = new EmptyResponse();
        $newResponse = $cookieManager->expireCookieByName($originalResponse, 'foo');

        $this->assertSame([
            'foo=; Path=/foo; Expires=Thu, 01 Jan 1970 00:00:01 GMT; HttpOnly',
        ], $newResponse->getHeader('Set-Cookie'));
    }

    public function testGetCookieWithExistentToken()
    {
        $cookie = new Cookie('foo');
        $cookie->set('bar', 'baz');
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->parseSignedToken('bat')->willReturn($cookie);
        $cookieManager = $this->createCookieManager($tokenManager->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getHeaderLine('Cookie')->willReturn('foo=bat');

        $this->assertSame($cookie, $cookieManager->getCookie($request->reveal(), 'foo'));
    }

    public function testGetCookieWithNonExistentToken()
    {
        $tokenManager = $this->prophesize(TokenManagerInterface::class);
        $tokenManager->parseSignedToken('bat')->willReturn(null);
        $cookieManager = $this->createCookieManager($tokenManager->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getHeaderLine('Cookie')->willReturn('foo=bat');

        $this->assertSame('foo', $cookieManager->getCookie($request->reveal(), 'foo')->getName());
    }

    private function createCookieManager(TokenManagerInterface $tokenManager, bool $secure = true) : CookieManager
    {
        if (null === $tokenManager) {
            $tokenManager = $this->prophesize(TokenManagerInterface::class)->reveal();
        }

        $clock = new FrozenClock(new DateTimeImmutable('@100'));

        return new CookieManager(
            new CookieSettings('/foo', $secure, 100),
            $tokenManager,
            $clock
        );
    }
}
