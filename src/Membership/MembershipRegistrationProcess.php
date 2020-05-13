<?php

namespace App\Membership;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class MembershipRegistrationProcess
{
    private const NEW_ADHERENT_UUID = 'membership.new_adherent_uuid';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function start(string $uuid): void
    {
        $this->session->set(self::NEW_ADHERENT_UUID, $uuid);
    }

    public function getAdherentUuid(): ?string
    {
        return $this->session->get(self::NEW_ADHERENT_UUID);
    }

    public function isStarted(): bool
    {
        return $this->session->has(self::NEW_ADHERENT_UUID);
    }

    public function terminate(): void
    {
        $this->session->remove(self::NEW_ADHERENT_UUID);
    }
}
