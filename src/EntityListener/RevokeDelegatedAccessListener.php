<?php

namespace App\EntityListener;

use App\Entity\Adherent;
use App\Entity\District;
use App\Repository\MyTeam\DelegatedAccessRepository;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class RevokeDelegatedAccessListener
{
    /** @var DelegatedAccessRepository */
    private $delegatedAccessRepository;

    public function __construct(DelegatedAccessRepository $delegatedAccessRepository)
    {
        $this->delegatedAccessRepository = $delegatedAccessRepository;
    }

    public function preUpdate(Adherent $adherent, PreUpdateEventArgs $args): void
    {
        if ($args->hasChangedField('managedDistrict')
            && $args->getOldValue('managedDistrict') instanceof District
            && null === $args->getNewValue('managedDistrict')
        ) {
            $this->delegatedAccessRepository->removeFromDelegator($adherent);
        }
    }
}
