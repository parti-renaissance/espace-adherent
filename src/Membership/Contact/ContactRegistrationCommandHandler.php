<?php

declare(strict_types=1);

namespace App\Membership\Contact;

use App\Entity\Adherent;
use App\Entity\Contact;
use App\Membership\MembershipRequest\AvecVousMembershipRequest;
use App\Membership\MembershipRequestHandler;
use App\Repository\AdherentRepository;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ContactRegistrationCommandHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private EntityManagerInterface $em;
    private ContactRepository $contactRepository;
    private MembershipRequestHandler $membershipRequestHandler;
    private AdherentRepository $adherentRepository;

    public function __construct(
        EntityManagerInterface $em,
        ContactRepository $contactRepository,
        MembershipRequestHandler $membershipRequestHandler,
        AdherentRepository $adherentRepository,
        LoggerInterface $logger,
    ) {
        $this->em = $em;
        $this->contactRepository = $contactRepository;
        $this->membershipRequestHandler = $membershipRequestHandler;
        $this->adherentRepository = $adherentRepository;
        $this->logger = $logger;
    }

    public function __invoke(ContactRegistrationCommand $command): void
    {
        /** @var Contact|null $contact */
        $contact = $this->contactRepository->findOneByUuid($command->getUuid());

        if (!$contact) {
            return;
        }

        $this->em->refresh($contact);

        if ($contact->isProcessed()) {
            return;
        }

        if (
            SourceEnum::AVECVOUS === $contact->getSource()
            && \in_array(InterestEnum::ACTION_TERRAIN, $contact->getInterests(), true)
        ) {
            $adherent = $this->adherentRepository->findOneByEmail($contact->getEmailAddress());

            if (!$adherent instanceof Adherent) {
                $adherent = $this->membershipRequestHandler->createAdherent(AvecVousMembershipRequest::createFromContact($contact));
            }

            $contact->setAdherent($adherent);
        }

        $contact->process();

        $this->em->flush();
        $this->em->clear();
    }
}
