<?php
declare(strict_types = 1);

namespace DASPRiD\PikkuleipaTest;

use CultuurNet\Clock\FrozenClock;
use DASPRiD\Pikkuleipa\Cookie;
use DASPRiD\Pikkuleipa\TokenManager;
use DateTimeImmutable;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use PHPUnit\Framework\TestCase;

class TokenManagerTest extends TestCase
{
    public function testGetSignedTokenWithExpire()
    {
        $cookie = new Cookie('foo');
        $cookie->set('bar', 'baz');

        $clock = new FrozenClock(new DateTimeImmutable('@100'));
        $tokenManager = new TokenManager(new Sha256(), 'foo', 'foo', new Parser(), $clock);
        $token = $tokenManager->getSignedToken($cookie, 100);

        $data = json_decode(base64_decode(explode('.', $token)[1]), true);
        $this->assertSame('foo', $data['sub']);
        $this->assertSame(100, $data['iat']);
        $this->assertSame(200, $data['exp']);
        $this->assertSame(['bar' => 'baz'], json_decode($data['dat'], true));
    }

    public function testGetSignedTokenWithoutExpire()
    {
        $cookie = new Cookie('foo');
        $cookie->set('bar', 'baz');

        $clock = new FrozenClock(new DateTimeImmutable('@100'));
        $tokenManager = new TokenManager(new Sha256(), 'foo', 'foo', new Parser(), $clock);
        $token = $tokenManager->getSignedToken($cookie);

        $data = json_decode(base64_decode(explode('.', $token)[1]), true);
        $this->assertArrayNotHasKey('exp', $data);
    }

    public function testParseProperlySignedToken()
    {
        $cookie = new Cookie('foo');
        $cookie->set('bar', 'baz');

        $clock = new FrozenClock(new DateTimeImmutable('@100'));
        $tokenManager = new TokenManager(new Sha256(), 'foo', 'foo', new Parser(), $clock);
        $cookie = $tokenManager->parseSignedToken((string) $tokenManager->getSignedToken($cookie, 100));

        $this->assertEquals(new DateTimeImmutable('@100'), $cookie->getIssuedAt());
        $this->assertSame('baz', $cookie->get('bar'));
        $this->assertSame('foo', $cookie->getName());
    }

    public function testParseMalformedToken()
    {
        $tokenManager = new TokenManager(new Sha256(), 'foo', 'foo');
        $this->assertNull($tokenManager->parseSignedToken('foo'));
    }

    public function testParseExpiredToken()
    {
        $clock = new FrozenClock(new DateTimeImmutable('@100'));
        $tokenManager = new TokenManager(new Sha256(), 'foo', 'foo', new Parser(), $clock);
        $this->assertNull(
            $tokenManager->parseSignedToken((string) $tokenManager->getSignedToken(new Cookie('foo'), -1))
        );
    }

    public function testIllegalToken()
    {
        $clock = new FrozenClock(new DateTimeImmutable('@100'));
        $tokenManager = new TokenManager(new Sha256(), 'foo', 'bar', new Parser(), $clock);
        $this->assertNull(
            $tokenManager->parseSignedToken((string) $tokenManager->getSignedToken(new Cookie('foo'), 1))
        );
    }
}
