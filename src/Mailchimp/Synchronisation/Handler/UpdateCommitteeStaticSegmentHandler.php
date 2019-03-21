<?php

namespace AppBundle\Mailchimp\Synchronisation\Handler;

use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;
use AppBundle\Mailchimp\Manager;
use AppBundle\Mailchimp\Synchronisation\Command\AddAdherentToCommitteeStaticSegmentCommand;
use AppBundle\Mailchimp\Synchronisation\Command\RemoveAdherentFromCommitteeStaticSegmentCommand;
use AppBundle\Mailchimp\Synchronisation\Command\UpdateCommitteeStaticSegmentCommandInterface;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateCommitteeStaticSegmentHandler implements MessageHandlerInterface
{
    private $mailchimpManager;
    private $adherentRepository;
    private $committeeRepository;
    private $entityManager;

    public function __construct(
        Manager $mailchimpManager,
        AdherentRepository $adherentRepository,
        CommitteeRepository $committeeRepository,
        ObjectManager $entityManager
    ) {
        $this->mailchimpManager = $mailchimpManager;
        $this->adherentRepository = $adherentRepository;
        $this->committeeRepository = $committeeRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateCommitteeStaticSegmentCommandInterface $command): void
    {
        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid($command->getAdherentUuid()->toString());
        $committee = $this->committeeRepository->findOneByUuid($command->getCommitteeUuid()->toString());

        if (!$adherent || !$committee) {
            return;
        }

        $this->entityManager->refresh($adherent);
        $this->entityManager->refresh($committee);

        if (!$committee->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(
                sprintf('Committee "%s" does not have Mailchimp static segment id', $committee->getUuidAsString())
            );
        }

        if ($command instanceof RemoveAdherentFromCommitteeStaticSegmentCommand) {
            $this->mailchimpManager->removeMemberFromStaticSegment($committee->getMailchimpId(), $adherent->getEmailAddress());
        } elseif ($command instanceof AddAdherentToCommitteeStaticSegmentCommand) {
            $this->mailchimpManager->addMemberToStaticSegment($committee->getMailchimpId(), $adherent->getEmailAddress());
        }

        $this->entityManager->clear();
    }
}
