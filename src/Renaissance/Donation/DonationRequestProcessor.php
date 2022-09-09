<?php

namespace App\Renaissance\Donation;

use App\Donation\DonationRequest;
use Symfony\Component\Workflow\StateMachine;

class DonationRequestProcessor
{
    private StateMachine $workflow;

    public function __construct(StateMachine $donationProcessWorkflow)
    {
        $this->workflow = $donationProcessWorkflow;
    }

    public function canChooseDonationAmount(DonationRequest $donationRequest): bool
    {
        return $this->can($donationRequest, DonationRequestStateEnum::TO_CHOOSE_DONATION_AMOUNT);
    }

    public function canFillPersonalInfo(DonationRequest $donationRequest): bool
    {
        return $this->can($donationRequest, DonationRequestStateEnum::TO_FILL_PERSONAL_INFO);
    }

    public function canAcceptTermsAndConditions(DonationRequest $donationRequest): bool
    {
        return $this->can($donationRequest, DonationRequestStateEnum::TO_ACCEPT_TERMS_AND_CONDITIONS);
    }

    public function canProceedDonationPayment(DonationRequest $donationRequest): bool
    {
        return $this->can($donationRequest, DonationRequestStateEnum::TO_PAY_DONATION);
    }

    public function canFinishDonationRequest(DonationRequest $donationRequest): bool
    {
        return $this->can($donationRequest, DonationRequestStateEnum::TO_FINISH);
    }

    public function doChooseDonationAmount(DonationRequest $command): void
    {
        $this->apply($command, DonationRequestStateEnum::TO_CHOOSE_DONATION_AMOUNT);
    }

    public function doFillPersonalInfo(DonationRequest $command): void
    {
        $this->apply($command, DonationRequestStateEnum::TO_FILL_PERSONAL_INFO);
    }

    public function doAcceptTermsAndConditions(DonationRequest $command): void
    {
        $this->apply($command, DonationRequestStateEnum::TO_ACCEPT_TERMS_AND_CONDITIONS);
    }

    public function doDonationPayment(DonationRequest $command): void
    {
        $this->apply($command, DonationRequestStateEnum::TO_PAY_DONATION);
    }

    public function doFinishDonationRequest(DonationRequest $command): void
    {
        $this->apply($command, DonationRequestStateEnum::TO_FINISH);
    }

    private function can(DonationRequest $command, string $transitionName): bool
    {
        return $this->workflow->can($command, $transitionName);
    }

    private function apply(DonationRequest $command, string $transitionName): void
    {
        $this->workflow->apply($command, $transitionName);
    }
}
