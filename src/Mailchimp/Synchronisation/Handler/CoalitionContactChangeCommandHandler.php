<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Coalition\CoalitionContactValueObject;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\CoalitionContactChangeCommand;
use App\Repository\AdherentRepository;
use App\Repository\Coalition\CauseFollowerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CoalitionContactChangeCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
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

    public function __invoke(CoalitionContactChangeCommand $message): void
    {
        $email = $message->getEmail();
        $contact = $message->isAdherent()
            ? $this->adherentRepository->findOneBy(['emailAddress' => $email])
            : $this->causeFollowerRepository->findOneBy(['emailAddress' => $email])
        ;

        if (!$contact) {
            $this->logger->warning(sprintf('Coalition contact with email "%s" not found, message skipped', $email));

            return;
        }

        $this->entityManager->refresh($contact);

        $valueObject = $message->isAdherent()
            ? CoalitionContactValueObject::createFromAdherent($contact)
            : CoalitionContactValueObject::createFromCauseFollower($contact)
        ;

        $this->manager->editCoalitionMember($valueObject);

        $this->entityManager->clear();
    }
}
