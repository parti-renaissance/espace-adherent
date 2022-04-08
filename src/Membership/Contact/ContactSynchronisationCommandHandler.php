<?php

namespace App\Membership\Contact;

use App\Entity\Contact;
use App\Repository\AdherentRepository;
use App\Repository\ContactRepository;
use App\SendInBlue\ContactManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ContactSynchronisationCommandHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private EntityManagerInterface $em;
    private ContactRepository $contactRepository;
    private AdherentRepository $adherentRepository;
    private ContactManager $contactManager;

    public function __construct(
        EntityManagerInterface $em,
        AdherentRepository $adherentRepository,
        ContactRepository $contactRepository,
        ContactManager $contactManager,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->contactRepository = $contactRepository;
        $this->adherentRepository = $adherentRepository;
        $this->contactManager = $contactManager;
        $this->logger = $logger;
    }

    public function __invoke(ContactSynchronisationCommand $command): void
    {
        /** @var Contact|null $contact */
        $contact = $this->contactRepository->findOneByUuid($command->getUuid());

        if (!$contact) {
            return;
        }

        $this->em->refresh($contact);

        // Do not synchronize on sendinblue if an adherent already exists with the same email
        if ($this->adherentRepository->findOneByEmail($contact->getEmailAddress())) {
            return;
        }

        try {
            $this->contactManager->synchronize($contact);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Failed to synchronize contact UUID: "%s". Error: %s', $contact->getUuid(), $e->getMessage()));
        }
    }
}
