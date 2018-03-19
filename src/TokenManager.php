<?php
declare(strict_types = 1);

namespace DASPRiD\Pikkuleipa;

use CultuurNet\Clock\Clock;
use CultuurNet\Clock\SystemClock;
use DASPRiD\Pikkuleipa\Exception\JsonException;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\ValidationData;

final class TokenManager implements TokenManagerInterface
{
    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var string
     */
    private $signatureKey;

    /**
     * @var string
     */
    private $verificationKey;

    /**
     * @var Parser
     */
    private $tokenParser;

    /**
     * @var Clock
     */
    private $clock;

    public function __construct(
        Signer $signer,
        string $signatureKey,
        string $verificationKey,
        Parser $tokenParser = null,
        Clock $clock = null
    ) {
        $this->signer = $signer;
        $this->signatureKey = $signatureKey;
        $this->verificationKey = $verificationKey;
        $this->tokenParser = $tokenParser ?: new Parser();
        $this->clock = $clock ?: new SystemClock(new DateTimeZone('UTC'));
    }

    public function getSignedToken(Cookie $cookie, ?int $lifetime = null) : string
    {
        $currentTimestamp = $this->clock->getDateTime()->getTimestamp();
        $builder = (new Builder())
            ->setIssuedAt($currentTimestamp)
            ->set('ews', $cookie->endsWithSession())
            ->setSubject($cookie->getName())
            ->set('dat', $cookie->toJson());

        if (null !== $lifetime) {
            $builder->setExpiration($currentTimestamp + $lifetime);
        }

        return (string) $builder->sign($this->signer, $this->signatureKey)->getToken();
    }

    public function parseSignedToken(string $serializedToken) : ?Cookie
    {
        try {
            $token = $this->tokenParser->parse($serializedToken);
        } catch (Exception $e) {
            return null;
        }

        if (! $token->validate(new ValidationData($this->clock->getDateTime()->getTimestamp()))) {
            return null;
        }

        if (! $token->verify($this->signer, $this->verificationKey)) {
            return null;
        }

        if (! $token->hasClaim('sub') ||
            ! $token->hasClaim('ews') ||
            ! $token->hasClaim('iat') ||
            ! $token->hasClaim('dat')
        ) {
            return null;
        }

        try {
            return Cookie::fromJson(
                $token->getClaim('sub'),
                $token->getClaim('ews'),
                new DateTimeImmutable('@' . $token->getClaim('iat')),
                $token->getClaim('dat')
            );
        } catch (JsonException $e) {
            return null;
        }
    }
}
