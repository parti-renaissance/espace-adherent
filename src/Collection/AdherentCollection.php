<?php

namespace App\Collection;

use App\Entity\Adherent;
use Doctrine\Common\Collections\ArrayCollection;

class AdherentCollection extends ArrayCollection
{
    public function merge(self $other): self
    {
        foreach ($other as $item) {
            $this->add($item);
        }

        return $this;
    }

    public function getCommitteesNotificationsSubscribers(): self
    {
        return $this->filter(function (Adherent $adherent) {
            return $adherent->hasSubscribedLocalHostEmails();
        });
    }
}
