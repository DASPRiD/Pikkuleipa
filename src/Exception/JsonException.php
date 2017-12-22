<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa\Exception;

use RuntimeException;

final class JsonException extends RuntimeException implements ExceptionInterface
{
    public static function fromJsonDecodeError(string $errorMessage) : self
    {
        return new self(sprintf('Could not decode JSON string: %s', $errorMessage));
    }

    public static function fromJsonEncodeError(string $errorMessage) : self
    {
        return new self(sprintf('Could not encode data to JSON: %s', $errorMessage));
    }
}
