<?php

namespace App\Adherent\Contribution;

use App\Adherent\AdherentRoleEnum;
use App\Entity\Adherent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\StateMachine;

class ContributionRequestProcessor
{
    private StateMachine $workflow;

    public function __construct(
        StateMachine $electedRepresentativeContributionProcessWorkflow,
        private readonly Security $security,
    ) {
        $this->workflow = $electedRepresentativeContributionProcessWorkflow;
    }

    public function canFillRevenue(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_FILL_REVENUE);
    }

    public function canNoContributionNeeded(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_NO_CONTRIBUTION_NEEDED);
    }

    public function canSeeContributionAmount(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_SEE_CONTRIBUTION_AMOUNT);
    }

    public function canFillContributionInformations(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_FILL_CONTRIBUTION_INFORMATIONS);
    }

    public function canCompleteContributionRequest(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_CONTRIBUTION_COMPLETE);
    }

    public function doFillRevenue(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_FILL_REVENUE);
    }

    public function doNoContributionNeeded(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_NO_CONTRIBUTION_NEEDED);
    }

    public function doContributionAlreadyDone(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_NO_CONTRIBUTION_NEEDED);
    }

    public function doSeeContributionAmount(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_SEE_CONTRIBUTION_AMOUNT);
    }

    public function doFillContributionInformations(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_FILL_CONTRIBUTION_INFORMATIONS);
    }

    public function doCompleteContributionRequest(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_CONTRIBUTION_COMPLETE);
    }

    private function can(ContributionRequest $command, string $transitionName): bool
    {
        $user = $this->security->getUser();

        if (
            !$user instanceof Adherent
            || !$this->security->isGranted(AdherentRoleEnum::ONGOING_ELECTED_REPRESENTATIVE)
        ) {
            return false;
        }

        return $this->workflow->can($command, $transitionName);
    }

    private function apply(ContributionRequest $command, string $transitionName): void
    {
        $this->workflow->apply($command, $transitionName);
    }
}
