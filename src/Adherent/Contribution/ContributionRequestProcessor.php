<?php

declare(strict_types=1);

namespace App\Adherent\Contribution;

use App\Adherent\AdherentRoleEnum;
use App\Entity\Adherent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\WorkflowInterface;

class ContributionRequestProcessor
{
    private WorkflowInterface $workflow;

    public function __construct(
        WorkflowInterface $contributionProcessStateMachine,
        private readonly Security $security,
    ) {
        $this->workflow = $contributionProcessStateMachine;
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
