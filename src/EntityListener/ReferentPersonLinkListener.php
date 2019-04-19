<?php

namespace AppBundle\EntityListener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ReferentPersonLinkListener
{
    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setAdherent(ReferentPersonLink $personLink, LifecycleEventArgs $args): void
    {
        $adherentRepository = $args->getEntityManager()->getRepository(Adherent::class);

        $email = $personLink->getEmail();
        $adherent = $adherentRepository->findOneByEmail($email);

        if ($adherent) {
            $personLink->setAdherent($adherent);
        } elseif ($personLink->getAdherent()) {
            if ($personLink->getAdherent()->getReferentTeamReferent()) {
                $personLink->getAdherent()->getReferentTeam(null);
            }
            $personLink->setAdherent(null);
        }
    }
}
