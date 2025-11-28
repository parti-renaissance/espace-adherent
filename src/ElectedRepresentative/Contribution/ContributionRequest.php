<?php

declare(strict_types=1);

namespace App\ElectedRepresentative\Contribution;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class ContributionRequest
{
    private const CONTRIBUTION_MIN_REVENUE_AMOUNT = 250;
    private const CONTRIBUTION_MAX_AMOUNT = 200;

    private string $state = ContributionRequestStateEnum::STATE_START;

    private ?int $adherentId = null;

    private bool $redeclare = false;

    #[Assert\GreaterThanOrEqual(value: 0, groups: ['fill_revenue'])]
    #[Assert\NotBlank(groups: ['fill_revenue'])]
    public ?int $revenueAmount = null;

    #[Assert\Length(min: 2, groups: ['fill_contribution_informations'])]
    #[Assert\NotBlank(groups: ['fill_contribution_informations'])]
    public ?string $accountName = null;

    #[Assert\Country(message: 'common.country.invalid', groups: ['fill_contribution_informations'])]
    #[Assert\NotBlank(groups: ['fill_contribution_informations'])]
    public ?string $accountCountry = AddressInterface::FRANCE;

    #[Assert\Iban(groups: ['fill_contribution_informations'])]
    #[Assert\NotBlank(groups: ['fill_contribution_informations'])]
    public ?string $iban = null;

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function isRedeclare(): bool
    {
        return $this->redeclare;
    }

    public function setRedeclare(bool $redeclare): void
    {
        $this->redeclare = $redeclare;
    }

    public function getAdherentId(): ?int
    {
        return $this->adherentId;
    }

    public function updateFromAdherent(Adherent $adherent): void
    {
        $this->adherentId = $adherent->getId();
    }

    public function needContribution(): bool
    {
        return $this->revenueAmount >= self::CONTRIBUTION_MIN_REVENUE_AMOUNT;
    }

    public function getContributionAmount(): int
    {
        if (!$this->needContribution()) {
            return 0;
        }

        $contributionAmount = (int) round($this->revenueAmount * 2 / 100);

        if ($contributionAmount > self::CONTRIBUTION_MAX_AMOUNT) {
            return self::CONTRIBUTION_MAX_AMOUNT;
        }

        return $contributionAmount;
    }

    public function getContributionAmountAfterTax(): float
    {
        return round($this->getContributionAmount() / 3);
    }
}
