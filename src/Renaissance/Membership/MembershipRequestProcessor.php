<?php

namespace App\Renaissance\Membership;

use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Symfony\Component\Workflow\StateMachine;

class MembershipRequestProcessor
{
    private StateMachine $workflow;

    public function __construct(StateMachine $membershipProcessWorkflow)
    {
        $this->workflow = $membershipProcessWorkflow;
    }

    public function canFillPersonalInfo(RenaissanceMembershipRequest $membershipRequest): bool
    {
        return $this->can($membershipRequest, MembershipRequestStateEnum::TO_FILL_PERSONAL_INFO);
    }

    public function canChooseAmount(RenaissanceMembershipRequest $membershipRequestCommand): bool
    {
        return $this->can($membershipRequestCommand, MembershipRequestStateEnum::TO_CHOOSE_ADHESION_AMOUNT);
    }

    public function canFillAdditionalInformations(RenaissanceMembershipRequest $membershipRequestCommand): bool
    {
        return $this->can($membershipRequestCommand, MembershipRequestStateEnum::TO_FILL_ADDITIONAL_INFORMATIONS);
    }

    public function canAcceptTermsAndConditions(RenaissanceMembershipRequest $membershipRequestCommand): bool
    {
        return $this->can($membershipRequestCommand, MembershipRequestStateEnum::TO_ACCEPT_TERMS_AND_CONDITIONS);
    }

    public function canValidSummary(RenaissanceMembershipRequest $membershipRequestCommand): bool
    {
        return $this->can($membershipRequestCommand, MembershipRequestStateEnum::TO_VALID_SUMMARY);
    }

    public function canPayMembership(RenaissanceMembershipRequest $membershipRequestCommand): bool
    {
        return $this->can($membershipRequestCommand, MembershipRequestStateEnum::TO_PAY_MEMBERSHIP);
    }

    public function canFinishMembershipRequest(RenaissanceMembershipRequest $membershipRequestCommand): bool
    {
        return $this->can($membershipRequestCommand, MembershipRequestStateEnum::TO_FINISH);
    }

    public function doFillPersonalInfo(RenaissanceMembershipRequest $command): void
    {
        $this->apply($command, MembershipRequestStateEnum::TO_FILL_PERSONAL_INFO);
    }

    public function doChooseAmount(RenaissanceMembershipRequest $command): void
    {
        $this->apply($command, MembershipRequestStateEnum::TO_CHOOSE_ADHESION_AMOUNT);
    }

    public function doFillAdditionalInformations(RenaissanceMembershipRequest $command): void
    {
        $this->apply($command, MembershipRequestStateEnum::TO_FILL_ADDITIONAL_INFORMATIONS);
    }

    public function doAcceptTermsAndConditions(RenaissanceMembershipRequest $command): void
    {
        $this->apply($command, MembershipRequestStateEnum::TO_ACCEPT_TERMS_AND_CONDITIONS);
    }

    public function doValidSummary(RenaissanceMembershipRequest $command): void
    {
        $this->apply($command, MembershipRequestStateEnum::TO_VALID_SUMMARY);
    }

    public function doPayMembership(RenaissanceMembershipRequest $command): void
    {
        $this->apply($command, MembershipRequestStateEnum::TO_PAY_MEMBERSHIP);
    }

    public function doFinishMembershipRequest(RenaissanceMembershipRequest $command): void
    {
        $this->apply($command, MembershipRequestStateEnum::TO_FINISH);
    }

    private function can(RenaissanceMembershipRequest $command, string $transitionName): bool
    {
        return $this->workflow->can($command, $transitionName);
    }

    private function apply(RenaissanceMembershipRequest $command, string $transitionName): void
    {
        $this->workflow->apply($command, $transitionName);
    }
}
