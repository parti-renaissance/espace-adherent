<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Coalition\CoalitionMemberValueObject;
use App\Entity\Adherent;
use App\Entity\Coalition\CauseFollower;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\CoalitionMemberChangeCommand;
use App\Repository\AdherentRepository;
use App\Repository\Coalition\CauseFollowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CoalitionMemberChangeCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private $manager;
    private $entityManager;
    private $adherentRepository;
    private $causeFollowerRepository;

    public function __construct(
        Manager $manager,
        AdherentRepository $adherentRepository,
        CauseFollowerRepository $causeFollowerRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->manager = $manager;
        $this->entityManager = $entityManager;
        $this->adherentRepository = $adherentRepository;
        $this->causeFollowerRepository = $causeFollowerRepository;
        $this->logger = new NullLogger();
    }

    public function __invoke(CoalitionMemberChangeCommand $message): void
    {
        $email = $message->getEmail();
        $contact = $message->isAdherent()
            ? $this->adherentRepository->findOneBy(['emailAddress' => $email])
            : $this->causeFollowerRepository->findLastOne($email)
        ;

        if (!$contact) {
            $this->logger->warning(sprintf('Coalition contact with email "%s" not found, message skipped', $email));

            return;
        }

        $this->entityManager->refresh($contact);

        // check if it's a coalition user
        if (($contact instanceof Adherent && (!$contact->isCoalitionsCguAccepted() && !$contact->isCoalitionUser()))
            || ($contact instanceof CauseFollower && !$contact->isCguAccepted())) {
            return;
        }

        $valueObject = $message->isAdherent()
            ? CoalitionMemberValueObject::createFromAdherent($contact)
            : CoalitionMemberValueObject::createFromCauseFollower($contact)
        ;

        $this->manager->editCoalitionMember($valueObject);

        $this->entityManager->clear();
    }
}
