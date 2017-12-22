<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa;

use DASPRiD\Pikkuleipa\Exception\JsonException;
use DateTimeImmutable;

final class Cookie
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var DateTimeImmutable
     */
    private $issuedAt;

    /**
     * @var array
     */
    private $data = [];

    public function __construct(string $name, ?DateTimeImmutable $issuedAt = null)
    {
        $this->name = $name;
        $this->issuedAt = $issuedAt ?: new DateTimeImmutable();
    }

    /**
     * Creates a new cookie instance from JSON encoded data.
     *
     * This is primarily used by the `TokenManager` to unserialize a cookie from a JWT token.
     *
     * @throws JsonException When an error occurs during decoding
     */
    public static function fromJson(string $name, DateTimeImmutable $issuedAt, string $json) : self
    {
        $cookie = new self($name, $issuedAt);
        $cookie->data = json_decode($json, true);

        if (! is_array($cookie->data)) {
            throw JsonException::fromJsonDecodeError(json_last_error_msg());
        }

        return $cookie;
    }

    /**
     * Serializes the cookie data as JSON.
     *
     * @throws JsonException When an error occurs during encoding
     */
    public function toJson() : string
    {
        $json = json_encode($this->data);

        if (false === $json) {
            throw JsonException::fromJsonEncodeError(json_last_error_msg());
        }

        return $json;
    }

    /**
     * Returns the name of the cookie.
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Returns when the cookie what issued.
     */
    public function getIssuedAt() : DateTimeImmutable
    {
        return $this->issuedAt;
    }

    /**
     * Returns a value from the cookie, or null if it doesn't exist.
     *
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Sets a value in the cookie.
     *
     * The value can be any scalar, array or object, which implements the `JsonSerializable` interface. Other values
     * will lead to an exception when trying to encode it for the response.
     *
     * @param mixed $value
     */
    public function set(string $name, $value) : void
    {
        $this->data[$name] = $value;
    }

    /**
     * Removes a value from the cookie.
     */
    public function remove(string $name) : void
    {
        unset($this->data[$name]);
    }
}
