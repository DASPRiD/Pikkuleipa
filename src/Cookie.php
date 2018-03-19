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
     * @var bool
     */
    private $endsWithSession;

    /**
     * @var array
     */
    private $data = [];

    public function __construct(string $name, bool $endsWithSession = false, ?DateTimeImmutable $issuedAt = null)
    {
        $this->name = $name;
        $this->endsWithSession = $endsWithSession;
        $this->issuedAt = $issuedAt ?: new DateTimeImmutable();
    }

    /**
     * Creates a new cookie instance from JSON encoded data.
     *
     * This is primarily used by the `TokenManager` to unserialize a cookie from a JWT token.
     *
     * @throws JsonException When an error occurs during decoding
     */
    public static function fromJson(
        string $name,
        bool $endsWithSession,
        DateTimeImmutable $issuedAt,
        string $json
    ) : self {
        $cookie = new self($name, $endsWithSession, $issuedAt);
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
     * Returns whether the cookie will end with the user session.
     */
    public function endsWithSession() : bool
    {
        return $this->endsWithSession;
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
