<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa\Exception;

use PHPUnit\Framework\TestCase;

class JsonExceptionTest extends TestCase
{
    public function testFromJsonDecodeError() : void
    {
        $exception = JsonException::fromJsonDecodeError('foo');
        $this->assertSame('Could not decode JSON string: foo', $exception->getMessage());
    }

    public function testFromJsonEncodeError() : void
    {
        $exception = JsonException::fromJsonEncodeError('foo');
        $this->assertSame('Could not encode data to JSON: foo', $exception->getMessage());
    }
}
