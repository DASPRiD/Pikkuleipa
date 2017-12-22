<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa\Factory;

use DASPRiD\Pikkuleipa\CookieManager;
use DASPRiD\Pikkuleipa\CookieManagerInterface;
use DASPRiD\Pikkuleipa\CookieSettings;
use DASPRiD\Pikkuleipa\TokenManagerInterface;
use DASPRiD\TreeReader\TreeReader;
use Psr\Container\ContainerInterface;

final class CookieManagerFactory
{
    public function __invoke(ContainerInterface $container) : CookieManagerInterface
    {
        $config = (new TreeReader($container->get('config')))->getChildren('pikkuleipa');

        $cookieManager = new CookieManager(
            $this->createCookieSettings($config->getChildren('default_cookie_settings')),
            $container->get(TokenManagerInterface::class)
        );

        if (! $config->hasKey('cookie_settings')) {
            return $cookieManager;
        }

        foreach ($config->getChildren('cookie_settings') as $cookieSettings) {
            $cookieManager = $cookieManager->withCookieSettings(
                $cookieSettings->getKey(),
                $this->createCookieSettings($cookieSettings->getChildren())
            );
        }

        return $cookieManager;
    }

    private function createCookieSettings(TreeReader $config) : CookieSettings
    {
        return new CookieSettings(
            $config->getString('path'),
            $config->getBool('secure'),
            $config->getInt('lifetime')
        );
    }
}
