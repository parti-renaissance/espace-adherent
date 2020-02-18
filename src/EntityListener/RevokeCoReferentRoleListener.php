<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentManagedArea;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use AppBundle\Entity\ReferentTeamMember;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class RevokeCoReferentRoleListener
{
    private $entityManager;
    private $needRevokeCoReferentRole = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function preUpdate(Adherent $adherent, PreUpdateEventArgs $args): void
    {
        $this->needRevokeCoReferentRole = $args->hasChangedField('managedArea')
            && $args->getOldValue('managedArea') instanceof ReferentManagedArea
            && null === $args->getNewValue('managedArea')
        ;
    }

    public function preFlush(Adherent $adherent): void
    {
        if (!$this->needRevokeCoReferentRole) {
            return;
        }

        $adherents = [];

        foreach ($this->entityManager->getRepository(ReferentTeamMember::class)->findBy(['referent' => $adherent]) as $member) {
            $this->entityManager->remove($member);
            $adherents[] = $member->getMember();
        }

        foreach ($this->entityManager->getRepository(ReferentPersonLink::class)->findBy(['adherent' => $adherents]) as $personLink) {
            $personLink->setIsCoReferent(false);
        }

        $this->needRevokeCoReferentRole = false;
    }
}
