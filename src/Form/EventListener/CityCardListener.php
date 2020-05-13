<?php

namespace App\Form\EventListener;

use App\Entity\Election\CityCard;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CityCardListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var CityCard $cityCard */
        $cityCard = $form->getData();

        $this->cleanEmptyCandidates($cityCard);
        $this->cleanEmptyPrevisions($cityCard);
        $this->cleanEmptyManagers($cityCard);
    }

    public function cleanEmptyCandidates(CityCard $cityCard): void
    {
        $firstCandidate = $cityCard->getFirstCandidate();
        if ($firstCandidate && $firstCandidate->isEmpty()) {
            $cityCard->removeFirstCandidate();
        }
    }

    public function cleanEmptyPrevisions(CityCard $cityCard): void
    {
        $candidateOptionPrevision = $cityCard->getCandidateOptionPrevision();
        if ($candidateOptionPrevision && $candidateOptionPrevision->isEmpty()) {
            $cityCard->removeCandidateOptionPrevision();
        }

        $preparationPrevision = $cityCard->getPreparationPrevision();
        if ($preparationPrevision && $preparationPrevision->isEmpty()) {
            $cityCard->removePreparationPrevision();
        }

        $thirdOptionPrevision = $cityCard->getThirdOptionPrevision();
        if ($thirdOptionPrevision && $thirdOptionPrevision->isEmpty()) {
            $cityCard->removeThirdOptionPrevision();
        }

        $candidatePrevision = $cityCard->getCandidatePrevision();
        if ($candidatePrevision && $candidatePrevision->isEmpty()) {
            $cityCard->removeCandidatePrevision();
        }

        $nationalPrevision = $cityCard->getNationalPrevision();
        if ($nationalPrevision && $nationalPrevision->isEmpty()) {
            $cityCard->removeNationalPrevision();
        }
    }

    public function cleanEmptyManagers(CityCard $cityCard): void
    {
        $headquartersManager = $cityCard->getHeadquartersManager();
        if ($headquartersManager && $headquartersManager->isEmpty()) {
            $cityCard->removeHeadquartersManager();
        }

        $politicalManager = $cityCard->getPoliticManager();
        if ($politicalManager && $politicalManager->isEmpty()) {
            $cityCard->removePoliticManager();
        }

        $taskForceManager = $cityCard->getTaskForceManager();
        if ($taskForceManager && $taskForceManager->isEmpty()) {
            $cityCard->removeTaskForceManager();
        }
    }
}
