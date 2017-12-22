<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa\Factory;

use DASPRiD\Pikkuleipa\TokenManager;
use DASPRiD\Pikkuleipa\TokenManagerInterface;
use DASPRiD\TreeReader\TreeReader;
use Psr\Container\ContainerInterface;

final class TokenManagerFactory
{
    public function __invoke(ContainerInterface $container) : TokenManagerInterface
    {
        $config = (new TreeReader($container->get('config')))->getChildren('pikkuleipa')->getChildren('token');

        $signerClass = $config->getString('signer_class');

        return new TokenManager(
            new $signerClass(),
            $config->getString('signature_key'),
            $config->getString('verification_key')
        );
    }
}
