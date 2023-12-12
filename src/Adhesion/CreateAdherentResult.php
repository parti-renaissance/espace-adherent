<?php

namespace App\Adhesion;

use Ramsey\Uuid\UuidInterface;

class CreateAdherentResult
{
    private const PAYMENT = 'payment';
    private const ACTIVATION = 'activation';
    private const ALREADY_EXISTS = 'already_exists';

    private ?string $nextStep = null;
    private ?UuidInterface $accountIdentifier = null;

    public static function createAlreadyExists(): self
    {
        return (new self())->setNextStep(self::ALREADY_EXISTS);
    }

    public static function createActivation(): self
    {
        return (new self())->setNextStep(self::ACTIVATION);
    }

    public static function createPayment(): self
    {
        return (new self())->setNextStep(self::PAYMENT);
    }

    public function isNextStepPayment(): bool
    {
        return self::PAYMENT === $this->nextStep;
    }

    public function isNextStepActivation(): bool
    {
        return self::ACTIVATION === $this->nextStep;
    }

    private function setNextStep(string $nextStep): self
    {
        $this->nextStep = $nextStep;

        return $this;
    }

    public function withAccountIdentifier(UuidInterface $uuid): self
    {
        $this->accountIdentifier = $uuid;

        return $this;
    }

    public function getAccountIdentifier(): ?UuidInterface
    {
        return $this->accountIdentifier;
    }
}
