<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentActivationToken;
use AppBundle\Entity\AdherentResetPasswordToken;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\CitizenProject;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\IdeasWorkshop\Vote;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Summary;
use AppBundle\Entity\Unregistration;
use AppBundle\History\EmailSubscriptionHistoryHandler;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdherentRegistry
{
    private $em;
    private $emailSubscriptionHistoryHandler;

    public function __construct(
        EntityManagerInterface $em,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler
    ) {
        $this->em = $em;
        $this->emailSubscriptionHistoryHandler = $emailSubscriptionHistoryHandler;
    }

    public function unregister(Adherent $adherent, Unregistration $unregistration): void
    {
        $this->em->beginTransaction();

        $this->em->persist($unregistration);

        $this->em->getRepository(CitizenProject::class)->unfollowCitizenProjectsOnUnregistration($adherent);
        $this->em->getRepository(Committee::class)->unfollowCommitteesOnUnregistration($adherent);

        $this->emailSubscriptionHistoryHandler->handleUnsubscriptions($adherent);

        $citizenActionRepository = $this->em->getRepository(CitizenAction::class);
        $citizenActionRepository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_PAST, true);
        $citizenActionRepository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_UPCOMING);

        $eventRepository = $this->em->getRepository(Event::class);
        $eventRepository->removeOrganizerEvents($adherent, EventRepository::TYPE_PAST, true);
        $eventRepository->removeOrganizerEvents($adherent, EventRepository::TYPE_UPCOMING);

        $ideaRepository = $this->em->getRepository(Idea::class);
        $ideaRepository->removeNotFinalizedIdeas($adherent);
        $ideaRepository->anonymizeFinalizedIdeas($adherent);

        $this->em->getRepository(Thread::class)->removeAuthorItems($adherent);
        $this->em->getRepository(ThreadComment::class)->removeAuthorItems($adherent);
        $this->em->getRepository(Vote::class)->removeAuthorItems($adherent);

        $this->em->getRepository(EventRegistration::class)->anonymizeAdherentRegistrations($adherent);
        $this->em->getRepository(CommitteeFeedItem::class)->removeAuthorItems($adherent);
        $this->em->getRepository(Report::class)->anonymizeAuthorReports($adherent);

        $this->em->getRepository(VolunteerRequest::class)->updateAdherentRelation($adherent->getEmailAddress(), null);
        $this->em->getRepository(RunningMateRequest::class)->updateAdherentRelation($adherent->getEmailAddress(), null);

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
