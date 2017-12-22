<?php
declare(strict_types = 1);

namespace DASPRiD\PikkuleipaTest\Factory;

use DASPRiD\Pikkuleipa\CookieSettings;
use DASPRiD\Pikkuleipa\Factory\CookieManagerFactory;
use DASPRiD\Pikkuleipa\TokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class CookieManagerFactoryTest extends TestCase
{
    public function testDefaultCookieSettings() : void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'pikkuleipa' => [
                'default_cookie_settings' => [
                    'path' => '/',
                    'secure' => true,
                    'lifetime' => 100,
                ],
            ],
        ]);
        $tokenManager = $this->prophesize(TokenManagerInterface::class)->reveal();
        $container->get(TokenManagerInterface::class)->willReturn($tokenManager);

        $factory = new CookieManagerFactory();
        $cookieManager = $factory($container->reveal());

        $this->assertAttributeEquals(new CookieSettings('/', true, 100), 'defaultCookieSettings', $cookieManager);
        $this->assertAttributeSame($tokenManager, 'tokenManager', $cookieManager);
    }

    public function testSpecificCookieSettings() : void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'pikkuleipa' => [
                'default_cookie_settings' => [
                    'path' => '/',
                    'secure' => true,
                    'lifetime' => 100,
                ],
                'cookie_settings' => [
                    'foo' => [
                        'path' => '/foo',
                        'secure' => true,
                        'lifetime' => 100,
                    ],
                    'bar' => [
                        'path' => '/bar',
                        'secure' => false,
                        'lifetime' => 200,
                    ],
                ],
            ],
        ]);
        $tokenManager = $this->prophesize(TokenManagerInterface::class)->reveal();
        $container->get(TokenManagerInterface::class)->willReturn($tokenManager);

        $factory = new CookieManagerFactory();
        $cookieManager = $factory($container->reveal());

        $this->assertAttributeEquals(new CookieSettings('/', true, 100), 'defaultCookieSettings', $cookieManager);
        $this->assertAttributeEquals([
            'foo' => new CookieSettings('/foo', true, 100),
            'bar' => new CookieSettings('/bar', false, 200),
        ], 'cookieSettings', $cookieManager);
        $this->assertAttributeSame($tokenManager, 'tokenManager', $cookieManager);
    }
}
