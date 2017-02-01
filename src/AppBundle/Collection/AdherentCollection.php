<?php

namespace AppBundle\Collection;

use AppBundle\Entity\Adherent;
use Doctrine\Common\Collections\ArrayCollection;

class AdherentCollection extends ArrayCollection
{
    public function getCommitteesNotificationsSubscribers(): self
    {
        return $this->filter(function (Adherent $adherent) {
            return $adherent->hasSubscribedLocalHostEmails();
        });
    }
}
