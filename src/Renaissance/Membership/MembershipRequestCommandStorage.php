<?php

namespace App\Renaissance\Membership;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MembershipRequestCommandStorage
{
    public const SESSION_KEY_COMMAND = 'membership_request.command';

    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function save(MembershipRequestCommand $membershipRequestCommand): void
    {
        $this->session->set(self::SESSION_KEY_COMMAND, $membershipRequestCommand);
    }

    public function clear(): void
    {
        $this->session->remove(self::SESSION_KEY_COMMAND);
    }

    public function getMembershipRequestCommand(): MembershipRequestCommand
    {
        $command = $this->session->get(self::SESSION_KEY_COMMAND);

        return $command instanceof MembershipRequestCommand ? $command : new MembershipRequestCommand();
    }
}
