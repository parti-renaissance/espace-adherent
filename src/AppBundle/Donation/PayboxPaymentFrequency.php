<?php

namespace AppBundle\Donation;

final class PayboxPaymentFrequency
{
    const DEFAULT_FREQUENCY = 1;
    const UNLIMITED_FREQUENCY = 0;
    const DONATION_FREQUENCIES = [self::UNLIMITED_FREQUENCY, 2, 6];

    private $frequency;

    public function __construct(int $frequency)
    {
        if (!in_array($frequency, self::DONATION_FREQUENCIES) && $frequency !== self::DEFAULT_FREQUENCY) {
            throw new \InvalidArgumentException('Your frequency is not allowed');
        }

        $this->frequency = $frequency;
    }

    public static function fromInteger(int $frequency): self
    {
        return new self($frequency);
    }

    public static function fromString(string $frequency): self
    {
        return new self(intval($frequency));
    }

    public static function isValid($frequency): bool
    {
        $frequencies = self::DONATION_FREQUENCIES;
        $frequencies[] = self::DEFAULT_FREQUENCY;

        if (is_numeric($frequency)) {
            return in_array(intval($frequency), $frequencies);
        }

        return false;
    }

    public function getFrequency(): int
    {
        return $this->frequency;
    }

    public function getLabelFrequency(): string
    {
        if ($this->frequency == self::UNLIMITED_FREQUENCY) {
            return sprintf('Durée illimitée');
        }

        return sprintf('Pendant %s mois', $this->frequency);
    }

    public function getPayboxSuffixCmd(float $amount): string
    {
        switch ($this->frequency) {
            case self::UNLIMITED_FREQUENCY:
                $frequency = '00';
                break;
            case self::DEFAULT_FREQUENCY:
                return '';
            default:
                $frequency = str_pad(intval($this->frequency) - 1, 2, '0', STR_PAD_LEFT);
        }

        return sprintf('PBX_2MONT%sPBX_NBPAIE%sPBX_FREQ01PBX_QUAND00', str_pad($amount, 10, '0', STR_PAD_LEFT), $frequency);
    }
}
