<?php

declare(strict_types=1);

namespace App\Adherent;

use App\Adherent\Unregistration\UnregistrationCommand;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Membership\MembershipRequestHandler;
use App\OAuth\TokenRevocationAuthority;

class UnregistrationManager
{
    public function __construct(
        private readonly MembershipRequestHandler $membershipRequestHandler,
        private readonly TokenRevocationAuthority $tokenRevocationAuthority,
    ) {
    }

    public function createUnregistrationCommand(Administrator $administrator): UnregistrationCommand
    {
        return new UnregistrationCommand(['Compte supprimé via action administrateur.'], null, $administrator, true);
    }

    public function terminateMembership(Adherent $adherent, UnregistrationCommand $unregistrationCommand): void
    {
        $this->tokenRevocationAuthority->revokeUserTokens($adherent);

        $this->membershipRequestHandler->terminateMembership($adherent, $unregistrationCommand, false);
    }
}
