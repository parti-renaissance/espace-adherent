<?php

namespace App\Donation;

class DonationValueObject
{
    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $subscription;

    public function __construct(\DateTimeInterface $date, int $amount, string $type, bool $subscription)
    {
        $this->date = $date;
        $this->amount = $amount;
        $this->type = $type;
        $this->subscription = $subscription;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getAmountInEuros(): int
    {
        return $this->amount / 100;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isSubscription(): bool
    {
        return $this->subscription;
    }
}
