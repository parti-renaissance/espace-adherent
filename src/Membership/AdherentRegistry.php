<?php

namespace AppBundle\Membership;

use AppBundle\CitizenAction\CitizenActionManager;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Committee\CommitteeManager;
use AppBundle\Committee\Feed\CommitteeFeedManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Summary;
use AppBundle\Entity\Unregistration;
use AppBundle\Event\EventManager;
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Report\ReportManager;
use Doctrine\ORM\EntityManagerInterface;

class AdherentRegistry
{
    private $em;
    private $citizenActionManager;
    private $eventManager;
    private $committeeManager;
    private $committeeFeedManager;
    private $activitySubscriptionManager;
    private $citizenProjectManager;
    private $reportManager;
    private $registrationManager;

    public function __construct(
        EntityManagerInterface $em,
        CitizenActionManager $citizenActionManager,
        EventManager $eventManager,
        CommitteeManager $committeeManager,
        CommitteeFeedManager $committeeFeedManager,
        CitizenProjectManager $citizenProjectManager,
        ReportManager $reportManager,
        EventRegistrationManager $registrationManager
    ) {
        $this->em = $em;
        $this->citizenActionManager = $citizenActionManager;
        $this->committeeManager = $committeeManager;
        $this->committeeFeedManager = $committeeFeedManager;
        $this->eventManager = $eventManager;
        $this->citizenProjectManager = $citizenProjectManager;
        $this->reportManager = $reportManager;
        $this->registrationManager = $registrationManager;
    }

    public function unregister(Adherent $adherent, Unregistration $unregistration): void
    {
        $token = $this->em->getRepository(AdherentActivationToken::class)->findOneBy(['adherentUuid' => $adherent->getUuid()->toString()]);
        $summary = $this->em->getRepository(Summary::class)->findOneForAdherent($adherent);

        $this->em->beginTransaction();

        $this->em->persist($unregistration);
        $this->removeAdherentMemberShips($adherent);
        $this->citizenActionManager->removeOrganizerCitizenActions($adherent);
        $this->eventManager->removeOrganizerEvents($adherent);
        $this->registrationManager->anonymizeAdherentRegistrations($adherent);
        $this->committeeFeedManager->removeAuthorItems($adherent);
        $this->citizenProjectManager->removeAuthorItems($adherent);
        $this->reportManager->anonymAuthorReports($adherent);

        if ($token) {
            $this->em->remove($token);
        }

        if ($summary) {
            $this->em->remove($summary);
        }

        $this->em->remove($adherent);
        $this->em->flush();

        $this->em->commit();
    }

    private function removeAdherentMemberShips(Adherent $adherent): void
    {
        $committeeRepository = $this->em->getRepository(Committee::class);

        foreach ($adherent->getMemberships() as $membership) {
            $committee = $committeeRepository->findOneBy(['uuid' => $membership->getCommitteeUuid()->toString()]);
            if ($committee) {
                $this->committeeManager->unfollowCommittee($adherent, $committee, false);
            }
        }
    }
}
