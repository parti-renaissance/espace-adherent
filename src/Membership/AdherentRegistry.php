<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Summary;
use AppBundle\Entity\Unregistration;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdherentRegistry
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function unregister(Adherent $adherent, Unregistration $unregistration): void
    {
        $this->em->beginTransaction();

        $this->em->persist($unregistration);

        $this->em->getRepository(CitizenProject::class)->unfollowCitizenProjectsOnUnregistration($adherent);
        $this->em->getRepository(Committee::class)->unfollowCommitteesOnUnregistration($adherent);

        $citizenActionRepository = $this->em->getRepository(CitizenAction::class);
        $citizenActionRepository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_PAST, true);
        $citizenActionRepository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_UPCOMING);

        $eventRepository = $this->em->getRepository(Event::class);
        $eventRepository->removeOrganizerEvents($adherent, EventRepository::TYPE_PAST, true);
        $eventRepository->removeOrganizerEvents($adherent, EventRepository::TYPE_UPCOMING);

        $this->em->getRepository(EventRegistration::class)->anonymizeAdherentRegistrations($adherent);
        $this->em->getRepository(CommitteeFeedItem::class)->removeAuthorItems($adherent);
        $this->em->getRepository(CitizenProjectComment::class)->removeForAuthor($adherent);
        $this->em->getRepository(Report::class)->anonymizeAuthorReports($adherent);

        if ($token = $this->em->getRepository(AdherentActivationToken::class)->findOneBy(['adherentUuid' => $adherent->getUuid()->toString()])) {
            $this->em->remove($token);
        }

        if ($token = $this->em->getRepository(AdherentResetPasswordToken::class)->findOneBy(['adherentUuid' => $adherent->getUuid()->toString()])) {
            $this->em->remove($token);
        }

        if ($summary = $this->em->getRepository(Summary::class)->findOneForAdherent($adherent)) {
            $this->em->remove($summary);
        }

        $this->em->remove($adherent);
        $this->em->flush();

        $this->em->commit();
    }
}
