<?php

namespace App\Renaissance\Membership;

use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MembershipRequestStorage
{
    public const SESSION_KEY_COMMAND = 'membership_request.command';

    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function save(RenaissanceMembershipRequest $membershipRequest): void
    {
        $this->session->set(self::SESSION_KEY_COMMAND, $membershipRequest);
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY_COMMAND);
    }

    public function getMembershipRequest(): RenaissanceMembershipRequest
    {
        if (($command = $this->session->get(self::SESSION_KEY_COMMAND)) instanceof RenaissanceMembershipRequest) {
            return $command;
        }

        return new RenaissanceMembershipRequest();
    }
}
