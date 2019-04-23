<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

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

        $personLink->setAdherent($adherent);
    }
}
