<?php

namespace AppBundle\Entity;

use AppBundle\Exception\AdherentTokenAlreadyUsedException;
use AppBundle\Exception\AdherentTokenExpiredException;
use AppBundle\Exception\AdherentTokenMismatchException;
use AppBundle\ValueObject\SHA1;
use Ramsey\Uuid\UuidInterface;

/**
 * An interface for adherents' temporary actionable token.
 */
interface AdherentExpirableTokenInterface
{
    /**
     * Generates a unique token for a given adherent uuid.
     */
    public static function generate(Adherent $adherent, string $lifetime = '+1 day'): self;

    /**
     * Returns the value of the token.
     */
    public function getValue(): SHA1;

    /**
     * Returns the adherent uuid tied to the token.
     */
    public function getAdherentUuid(): UuidInterface;

    /**
     * Returns the date the token was used is any.
     *
     * @return \DateTime|\DateTime|null
     */
    public function getUsageDate();

    /**
     * Expires a token if it's not already.
     *
     * @throws AdherentTokenAlreadyUsedException If the token has already been used
     * @throws AdherentTokenExpiredException     If the token is expired
     * @throws AdherentTokenMismatchException    If the adherent is not tied to this token
     */
    public function consume(Adherent $adherent);

    /**
     * Returns a string representation of the token type.
     */
    public function getType(): string;
}
