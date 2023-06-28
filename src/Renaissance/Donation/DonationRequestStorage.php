<?php

namespace App\Renaissance\Donation;

use App\Donation\Request\DonationRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DonationRequestStorage
{
    public const SESSION_KEY_COMMAND = 'donation_request.command';

    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function save(DonationRequest $donationRequest): void
    {
        $this->session->set(self::SESSION_KEY_COMMAND, $donationRequest);
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY_COMMAND);
    }

    public function getDonationRequest(): DonationRequest
    {
        if (($command = $this->session->get(self::SESSION_KEY_COMMAND)) instanceof DonationRequest) {
            return $command;
        }

        return new DonationRequest();
    }
}
