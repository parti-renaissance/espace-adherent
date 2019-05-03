<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

class PersonLinkAdherentAttachListener
{
    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setAdherent(ReferentPersonLink $personLink, LifecycleEventArgs $args): void
    {
        if ($personLink->getAdherent() && $personLink->getEmail() === $personLink->getAdherent()->getEmailAddress()) {
            return;
        }

        $adherent = $args->getEntityManager()
            ->getRepository(Adherent::class)
            ->findOneByEmail($personLink->getEmail())
        ;

        if ($adherent) {
            $personLink->setAdherent($adherent);
        } elseif ($personLink->getAdherent()) {
            if ($personLink->getAdherent()->getReferentOfReferentTeam()) {
                $personLink->getAdherent()->setReferentTeamMember(null);
            }
            $personLink->setAdherent(null);
        }
    }
}
