<?php

namespace AppBundle\Adherent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\BannedAdherent;
use AppBundle\Membership\MembershipRequestHandler;
use AppBundle\Membership\UnregistrationCommand;
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

    public function ban(Adherent $adherent): void
    {
        $reason = sprintf('Exclu par la Commission des conflits le %s', date('d-m-Y'));

        $unregistrationHandler = new UnregistrationCommand();
        $unregistrationHandler->setReasons([$reason]);
        $unregistrationHandler->setComment($reason);

        $this->membershipRequestHandler->terminateMembership($unregistrationHandler, $adherent, false);

        $adherentBanned = BannedAdherent::createFromAdherent($adherent);
        $this->entityManager->persist($adherentBanned);
        $this->entityManager->flush();
    }

    public function canBan(Adherent $adherent): bool
    {
        return [] === array_diff($adherent->getRoles(), ['ROLE_ADHERENT', 'ROLE_USER']);
    }
}
