<?php
declare(strict_types = 1);

namespace DASPRiD\PikkuleipaTest;

use DASPRiD\Pikkuleipa\ConfigProvider;
use DASPRiD\Pikkuleipa\CookieManagerInterface;
use DASPRiD\Pikkuleipa\TokenManagerInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testInvoke() : void
    {
        $this->assertSame([
            'dependencies' => (new ConfigProvider())->getDependencyConfig(),
        ], (new ConfigProvider())->__invoke());
    }

    public function testGetDependencyConfig() : void
    {
        $dependencyConfig = (new ConfigProvider())->getDependencyConfig();
        $this->assertArrayHasKey('factories', $dependencyConfig);
        $this->assertArrayHasKey(CookieManagerInterface::class, $dependencyConfig['factories']);
        $this->assertArrayHasKey(TokenManagerInterface::class, $dependencyConfig['factories']);
    }
}
