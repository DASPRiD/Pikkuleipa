<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa;

use DASPRiD\Pikkuleipa\Factory\CookieManagerFactory;
use DASPRiD\Pikkuleipa\Factory\TokenManagerFactory;

final class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig() : array
    {
        return [
            'factories' => [
                CookieManagerInterface::class => CookieManagerFactory::class,
                TokenManagerInterface::class => TokenManagerFactory::class,
            ],
        ];
    }
}
