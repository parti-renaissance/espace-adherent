<?php

namespace App\Renaissance\Membership;

use Symfony\Component\Workflow\StateMachine;

class MembershipRequestCommandProcessor
{
    private StateMachine $workflow;

    public function __construct(StateMachine $membershipProcessWorkflow)
    {
        $this->workflow = $membershipProcessWorkflow;
    }

    public function canFillPersonalInfo(MembershipRequestCommand $membershipRequestCommand): bool
    {
        return $membershipRequestCommand->isFillPersonalInfo() || $this->can($membershipRequestCommand, MembershipRequestCommandStateEnum::TO_FILL_PERSONAL_INFO);
    }

    public function canChooseAmount(MembershipRequestCommand $membershipRequestCommand): bool
    {
        return $membershipRequestCommand->isChooseAmount() || $this->can($membershipRequestCommand, MembershipRequestCommandStateEnum::TO_CHOOSE_ADHESION_AMOUNT);
    }

    public function canAcceptTermsAndConditions(MembershipRequestCommand $membershipRequestCommand): bool
    {
        return $membershipRequestCommand->isTermsAndConditions() || $this->can($membershipRequestCommand, MembershipRequestCommandStateEnum::TO_ACCEPT_TERMS_AND_CONDITIONS);
    }

    public function canValidSummary(MembershipRequestCommand $membershipRequestCommand): bool
    {
        return $membershipRequestCommand->isSummary() || $this->can($membershipRequestCommand, MembershipRequestCommandStateEnum::TO_VALID_SUMMARY);
    }

    public function canPayMembership(MembershipRequestCommand $membershipRequestCommand): bool
    {
        return $membershipRequestCommand->isPayment() || $this->can($membershipRequestCommand, MembershipRequestCommandStateEnum::TO_PAY_MEMBERSHIP);
    }

    public function canFinishMembershipRequest(MembershipRequestCommand $membershipRequestCommand): bool
    {
        return $membershipRequestCommand->isFinish() || $this->can($membershipRequestCommand, MembershipRequestCommandStateEnum::TO_FINISH);
    }

    public function doFillPersonalInfo(MembershipRequestCommand $command): void
    {
        if (!$command->isFillPersonalInfo()) {
            $this->apply($command, MembershipRequestCommandStateEnum::TO_FILL_PERSONAL_INFO);
        }
    }

    public function doChooseAmount(MembershipRequestCommand $command): void
    {
        if (!$command->isChooseAmount()) {
            $this->apply($command, MembershipRequestCommandStateEnum::TO_CHOOSE_ADHESION_AMOUNT);
        }
    }

    public function doAcceptTermsAndConditions(MembershipRequestCommand $command): void
    {
        if (!$command->isTermsAndConditions()) {
            $this->apply($command, MembershipRequestCommandStateEnum::TO_ACCEPT_TERMS_AND_CONDITIONS);
        }
    }

    public function doValidSummary(MembershipRequestCommand $command): void
    {
        if (!$command->isSummary()) {
            $this->apply($command, MembershipRequestCommandStateEnum::TO_VALID_SUMMARY);
        }
    }

    public function doPayMembership(MembershipRequestCommand $command): void
    {
        if (!$command->isPayment()) {
            $this->apply($command, MembershipRequestCommandStateEnum::TO_PAY_MEMBERSHIP);
        }
    }

    public function doFinishMembershipRequest(MembershipRequestCommand $command): void
    {
        if (!$command->isFinish()) {
            $this->apply($command, MembershipRequestCommandStateEnum::TO_FINISH);
        }
    }

    public function handleStepTransit()
    {
    }

    private function can(MembershipRequestCommand $command, string $transitionName): bool
    {
        return $this->workflow->can($command, $transitionName);
    }

    private function apply(MembershipRequestCommand $command, string $transitionName): void
    {
        $this->workflow->apply($command, $transitionName);
    }
}
