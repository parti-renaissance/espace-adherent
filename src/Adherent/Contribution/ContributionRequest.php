<?php

namespace App\Adherent\Contribution;

use App\Address\Address;
use App\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class ContributionRequest
{
    private const CONTRIBUTION_MIN_REVENUE_AMOUNT = 250;
    private const CONTRIBUTION_MAX_AMOUNT = 200;

    private string $state = ContributionRequestStateEnum::STATE_START;

    private ?int $adherentId = null;

    private bool $redeclare = false;

    /**
     * @Assert\NotBlank(groups={"fill_revenue"})
     * @Assert\GreaterThanOrEqual(
     *     value=1,
     *     message="elected_representative.contributation.amount.greater_than_1",
     *     groups={"fill_revenue"}
     * )
     */
    public ?int $revenueAmount = null;

    /**
     * @Assert\NotBlank(groups={"fill_contribution_informations"})
     * @Assert\Length(min=2, groups={"fill_contribution_informations"})
     */
    public ?string $accountName = null;

    /**
     * @Assert\NotBlank(groups={"fill_contribution_informations"})
     * @Assert\Country(message="common.country.invalid", groups={"fill_contribution_informations"})
     */
    public ?string $accountCountry = Address::FRANCE;

    /**
     * @Assert\NotBlank(groups={"fill_contribution_informations"})
     * @Assert\Iban(groups={"fill_contribution_informations"})
     */
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
