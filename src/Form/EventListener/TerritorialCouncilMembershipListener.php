<?php

namespace App\Form\EventListener;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TerritorialCouncilMembershipListener implements EventSubscriberInterface
{
    /** @var Adherent */
    private $adherent;

    public function __construct(Adherent $adherent)
    {
        $this->adherent = $adherent;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'onPostSetData',
            FormEvents::SUBMIT => 'onSubmit',
        ];
    }

    public function onPostSetData(FormEvent $event): void
    {
        if (!$this->adherent || !($membership = $this->adherent->getTerritorialCouncilMembership())) {
            return;
        }

        $territorialCouncil = $membership->getTerritorialCouncil();
        $form = $event->getForm();
        $form->get('referentTerritorialCouncil')->setData(
            $this->adherent->isTerritorialCouncilReferentMember() ? $territorialCouncil : null);
        $form->get('lreManagerTerritorialCouncil')->setData(
            $this->adherent->isTerritorialCouncilLreManagerMember() ? $territorialCouncil : null);
        $form->get('referentJamTerritorialCouncil')->setData(
            $this->adherent->isTerritorialCouncilReferentJamMember() ? $territorialCouncil : null);
        $form->get('governmentMemberTerritorialCouncil')->setData(
            $this->adherent->isTerritorialCouncilGovernmentMemberMember() ? $territorialCouncil : null);
    }

    public function onSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        /** @var Adherent $adherent */
        $adherent = $form->getData();

        $referentTC = $form->get('referentTerritorialCouncil')->getData();
        $lreManagerTC = $form->get('lreManagerTerritorialCouncil')->getData();
        $referentJamTC = $form->get('referentJamTerritorialCouncil')->getData();
        $governmentMemberTC = $form->get('governmentMemberTerritorialCouncil')->getData();

        /** @var TerritorialCouncil $tc */
        $tc = $referentTC ?? $lreManagerTC ?? $referentJamTC ?? $governmentMemberTC ?? null;
        if (!$tc) {
            $adherent->revokeTerritorialCouncilMembership();

            return;
        }

        $qualityName = $referentTC
            ? TerritorialCouncilQualityEnum::REFERENT
            : ($lreManagerTC
                ? TerritorialCouncilQualityEnum::LRE_MANAGER
                : ($referentJamTC
                    ? TerritorialCouncilQualityEnum::REFERENT_JAM
                    : ($governmentMemberTC ? TerritorialCouncilQualityEnum::GOVERNMENT_MEMBER : null)));

        $quality = new TerritorialCouncilQuality($qualityName, $tc->getNameCodes());
        if (!$adherent->hasTerritorialCouncilMembership()) {
            $tcMembership = new TerritorialCouncilMembership($tc);
            $tcMembership->addQuality($quality);
            $adherent->setTerritorialCouncilMembership($tcMembership);

            return;
        }

        if ($adherent->getTerritorialCouncilMembership()->getTerritorialCouncil()->getId() === $tc->getId()) {
            if ($adherent->getTerritorialCouncilMembership()->hasQuality($qualityName)) {
                return;
            }

            $this->updateQuality($adherent->getTerritorialCouncilMembership(), TerritorialCouncilQualityEnum::REFERENT, $referentTC);
            $this->updateQuality($adherent->getTerritorialCouncilMembership(), TerritorialCouncilQualityEnum::LRE_MANAGER, $lreManagerTC);
            $this->updateQuality($adherent->getTerritorialCouncilMembership(), TerritorialCouncilQualityEnum::REFERENT_JAM, $referentJamTC);
            $this->updateQuality($adherent->getTerritorialCouncilMembership(), TerritorialCouncilQualityEnum::GOVERNMENT_MEMBER, $governmentMemberTC);
        }

        $adherent->getTerritorialCouncilMembership()->setTerritorialCouncil($tc);
        $adherent->getTerritorialCouncilMembership()->clearQualities();
        $adherent->getTerritorialCouncilMembership()->addQuality($quality);
    }

    private function updateQuality(
        TerritorialCouncilMembership $membership,
        string $qualityName,
        TerritorialCouncil $tc = null
    ): void {
        if ($tc) {
            $quality = new TerritorialCouncilQuality($qualityName, $tc->getNameCodes());
            $membership->addQuality($quality);
        } else {
            $membership->removeQualityWithName($qualityName);
        }
    }
}
