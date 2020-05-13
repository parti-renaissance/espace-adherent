<?php

namespace App\Adherent;

use App\Adherent\Unregistration\UnregistrationCommand;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\BannedAdherent;
use App\Membership\MembershipRequestHandler;
use Doctrine\Common\Persistence\ObjectManager;

class BanManager
{
    private $membershipRequestHandler;
    private $entityManager;

    public function __construct(MembershipRequestHandler $membershipRequestHandler, ObjectManager $entityManager)
    {
        $this->membershipRequestHandler = $membershipRequestHandler;
        $this->entityManager = $entityManager;
    }

    public function ban(Adherent $adherent, Administrator $administrator): void
    {
        $reason = sprintf('Exclu(e) par la Commission des conflits le %s', date('d-m-Y'));

        $unregistrationCommand = new UnregistrationCommand();
        $unregistrationCommand->setReasons([$reason]);
        $unregistrationCommand->setComment($reason);
        $unregistrationCommand->setExcludedBy($administrator);

        $this->membershipRequestHandler->terminateMembership($unregistrationCommand, $adherent, false);

        $adherentBanned = BannedAdherent::createFromAdherent($adherent);
        $this->entityManager->persist($adherentBanned);
        $this->entityManager->flush();
    }

    public function canBan(Adherent $adherent): bool
    {
        return [] === array_diff($adherent->getRoles(), ['ROLE_ADHERENT', 'ROLE_USER']);
    }
}
