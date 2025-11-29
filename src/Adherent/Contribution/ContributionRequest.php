<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class ContributionRequest
{
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
        $this->revenueAmount = $adherent->getLastRevenueDeclaration()?->amount;
    }

    public function needContribution(): bool
    {
        return ContributionAmountUtils::needContribution($this->revenueAmount);
    }

    public function getContributionAmount(): int
    {
        return ContributionAmountUtils::getContributionAmount($this->revenueAmount);
    }

    public function getContributionAmountAfterTax(): int
    {
        return ContributionAmountUtils::getContributionAmountAfterTax($this->revenueAmount);
    }
}
