<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ManagedArea\CommunicationManagerManagedArea;
use AppBundle\Entity\ManagedArea\DeputyManagedArea;
use AppBundle\Entity\ManagedArea\ElectedOfficerManagedArea;
use AppBundle\Entity\ManagedArea\ManagedArea;
use AppBundle\Entity\ManagedArea\ReferentManagedArea;
use AppBundle\Entity\ManagedArea\SenatorManagedArea;
use AppBundle\Entity\ReferentTag;
use AppBundle\Form\Admin\ManagedAreaCollectionType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\DataTransformerInterface;

class ManagedAreaCollectionTransformer implements DataTransformerInterface
{
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($managedAreaCollection)
    {
        if (!$managedAreaCollection) {
            return;
        }

        return [
            ManagedAreaCollectionType::DEPUTY_DISTRICT => $this->adherent->getDeputyManagedArea(),
            ManagedAreaCollectionType::COMMUNICATION_MANAGER_TAGS => array_map(function (CommunicationManagerManagedArea $managedArea) {
                return $managedArea->getTag();
            }, $this->adherent->getCommunicationManagerManagedAreas()->toArray()),
            ManagedAreaCollectionType::ELECTED_OFFICER_TAGS => array_map(function(ElectedOfficerManagedArea $managedArea) {
                return $managedArea->getTag();
            }, $this->adherent->getElectedOfficerManagedAreas()->toArray()),
            ManagedAreaCollectionType::REFERENT_TAGS => array_map(function(ReferentManagedArea $managedArea) {
                return $managedArea->getTag();
            }, $this->adherent->getReferentManagedAreas()->toArray()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($selectedManagedAreas)
    {
        $newManagedAreas = new ArrayCollection();

        // Referent
        foreach ($selectedManagedAreas[ManagedAreaCollectionType::REFERENT_TAGS] as $tag) {
            $newManagedAreas->add($this->getManagedAreaForTag(ReferentManagedArea::class, $tag));
        }

        // Elected Officer
        foreach ($selectedManagedAreas[ManagedAreaCollectionType::ELECTED_OFFICER_TAGS] as $tag) {
            $newManagedAreas->add($this->getManagedAreaForTag(ElectedOfficerManagedArea::class, $tag));
        }

        // Communication Manager
        foreach ($selectedManagedAreas[ManagedAreaCollectionType::COMMUNICATION_MANAGER_TAGS] as $tag) {
            $newManagedAreas->add($this->getManagedAreaForTag(CommunicationManagerManagedArea::class, $tag));
        }

        // Senator
        foreach ($selectedManagedAreas[ManagedAreaCollectionType::SENATOR_TAGS] as $tag) {
            $newManagedAreas->add($this->getManagedAreaForTag(SenatorManagedArea::class, $tag));
        }

        // Deputy
        $district = $selectedManagedAreas[ManagedAreaCollectionType::DEPUTY_DISTRICT];
        if ($this->adherent->getDeputyManagedArea() !== $district) {
            $newManagedAreas->add(new DeputyManagedArea($this->adherent, $district));
        }

        return $newManagedAreas;
    }

    private function getManagedAreaForTag(string $managedAreaType, ReferentTag $tag): ManagedArea
    {
        switch ($managedAreaType) {
            case ReferentManagedArea::class:
                $managedAreas = $this->adherent->getReferentManagedAreas();
                break;
            case ElectedOfficerManagedArea::class:
                $managedAreas = $this->adherent->getElectedOfficerManagedAreas();
                break;
            case CommunicationManagerManagedArea::class:
                $managedAreas = $this->adherent->getCommunicationManagerManagedAreas();
                break;
            case SenatorManagedArea::class:
                $managedAreas = $this->adherent->getSenatorManagedAreas();
                break;
            default:
                throw new \InvalidArgumentException("ManagedArea of type $managedAreaType is not supported.");
        }

        foreach ($managedAreas as $managedArea) {
            if ($managedArea->getTag() === $tag) {
                return $managedArea;
            }
        }

        return new $managedAreaType($this->adherent, $tag);
    }
}
