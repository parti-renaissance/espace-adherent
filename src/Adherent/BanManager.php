<?php

namespace App\Adherent;

use App\Adherent\Unregistration\UnregistrationCommand;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\BannedAdherent;
use App\Membership\MembershipRequestHandler;
use App\OAuth\TokenRevocationAuthority;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;

class BanManager
{
    public function __construct(
        private readonly MembershipRequestHandler $membershipRequestHandler,
        private readonly ObjectManager $entityManager,
        private readonly TokenRevocationAuthority $tokenRevocationAuthority
    ) {
    }

    public function ban(Adherent $adherent, Administrator $administrator): void
    {
        $this->tokenRevocationAuthority->revokeUserTokens($adherent);

        $reason = sprintf('Exclu(e) par la Commission des conflits le %s', date('d-m-Y'));

        $unregistrationCommand = new UnregistrationCommand([$reason], $reason, $administrator);

        $this->membershipRequestHandler->terminateMembership($adherent, $unregistrationCommand);

        $this->entityManager->persist(BannedAdherent::createFromAdherent($adherent));
        $this->entityManager->flush();
    }

    public function canBan(Adherent $adherent): bool
    {
        return !$adherent->isToDelete() && [] === array_diff($adherent->getRoles(), ['ROLE_RENAISSANCE_USER', 'ROLE_ADHERENT', 'ROLE_USER']);
    }
}
