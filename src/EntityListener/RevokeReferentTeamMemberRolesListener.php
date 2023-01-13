<?php

namespace App\EntityListener;

use App\Entity\Adherent;
use App\Entity\ReferentManagedArea;
use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use App\Entity\ReferentTeamMember;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class RevokeReferentTeamMemberRolesListener
{
    private $entityManager;
    private $needRevokeRoles = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function preUpdate(Adherent $adherent, PreUpdateEventArgs $args): void
    {
        $this->needRevokeRoles = $args->hasChangedField('managedArea')
            && $args->getOldValue('managedArea') instanceof ReferentManagedArea
            && null === $args->getNewValue('managedArea')
        ;
    }

    public function preFlush(Adherent $adherent): void
    {
        if (!$this->needRevokeRoles) {
            return;
        }

        // Revoke Co-Referent role
        foreach ($this->entityManager->getRepository(ReferentTeamMember::class)->findBy(['referent' => $adherent]) as $member) {
            $this->entityManager->remove($member);
        }

        foreach ($this->entityManager->getRepository(ReferentPersonLink::class)->findByReferentEmail($adherent->getEmailAddress()) as $personLink) {
            $personLink->setCoReferent(null);
        }

        $this->needRevokeRoles = false;
    }
}
