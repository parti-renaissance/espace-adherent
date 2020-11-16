<?php

namespace App\EntityListener;

use App\Entity\Adherent;
use App\Entity\District;
use App\Entity\ReferentManagedArea;
use App\Entity\SenatorArea;
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
        if (
            $this->deputyLostAccess($args) ||
            $this->senatorLostAccess($args) ||
            $this->referentLostAccess($args)
        ) {
            $this->delegatedAccessRepository->removeFromDelegator($adherent);
        }
    }

    protected function deputyLostAccess(PreUpdateEventArgs $args): bool
    {
        return $args->hasChangedField('managedDistrict')
            && $args->getOldValue('managedDistrict') instanceof District
            && null === $args->getNewValue('managedDistrict');
    }

    protected function senatorLostAccess(PreUpdateEventArgs $args): bool
    {
        return $args->hasChangedField('senatorArea')
            && $args->getOldValue('senatorArea') instanceof SenatorArea
            && null === $args->getNewValue('senatorArea');
    }

    protected function referentLostAccess(PreUpdateEventArgs $args): bool
    {
        return $args->hasChangedField('managedArea')
            && $args->getOldValue('managedArea') instanceof ReferentManagedArea
            && null === $args->getNewValue('managedArea');
    }
}
