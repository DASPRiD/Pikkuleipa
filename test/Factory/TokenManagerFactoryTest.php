<?php
declare(strict_types = 1);

namespace DASPRiD\PikkuleipaTest\Factory;

use DASPRiD\Pikkuleipa\Factory\TokenManagerFactory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class TokenManagerFactoryTest extends TestCase
{
    public function testInjection() : void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn([
            'pikkuleipa' => [
                'token' => [
                    'signer_class' => Sha256::class,
                    'signature_key' => 'foo',
                    'verification_key' => 'bar',
                ],
            ],
        ]);

        $factory = new TokenManagerFactory();
        $tokenManager = $factory($container->reveal());

        $this->assertAttributeInstanceOf(Sha256::class, 'signer', $tokenManager);
        $this->assertAttributeSame('foo', 'signatureKey', $tokenManager);
        $this->assertAttributeSame('bar', 'verificationKey', $tokenManager);
    }
}
