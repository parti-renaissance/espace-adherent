<?php

namespace App\ElectedRepresentative\Contribution;

use App\Entity\Adherent;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\StateMachine;

class ContributionRequestProcessor
{
    private StateMachine $workflow;

    public function __construct(
        StateMachine $electedRepresentativeContributionProcessWorkflow,
        private readonly Security $security
    ) {
        $this->workflow = $electedRepresentativeContributionProcessWorkflow;
    }

    public function canFillRevenue(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_FILL_REVENUE);
    }

    public function canSeeContributionAmount(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_SEE_CONTRIBUTION_AMOUNT);
    }

    public function canFillContributionInformations(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_FILL_CONTRIBUTION_INFORMATIONS);
    }

    public function canFinishContributionRequest(ContributionRequest $contributionRequest): bool
    {
        return $this->can($contributionRequest, ContributionRequestStateEnum::TO_FINISH);
    }

    public function doFillRevenue(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_FILL_REVENUE);
    }

    public function doSeeContributionAmount(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_SEE_CONTRIBUTION_AMOUNT);
    }

    public function doFillContributionInformations(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_FILL_CONTRIBUTION_INFORMATIONS);
    }

    public function doFinishContributionRequest(ContributionRequest $command): void
    {
        $this->apply($command, ContributionRequestStateEnum::TO_FINISH);
    }

    private function can(ContributionRequest $command, string $transitionName): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof Adherent || !$user->isElected()) {
            return false;
        }

        return $this->workflow->can($command, $transitionName);
    }

    private function apply(ContributionRequest $command, string $transitionName): void
    {
        $this->workflow->apply($command, $transitionName);
    }
}
