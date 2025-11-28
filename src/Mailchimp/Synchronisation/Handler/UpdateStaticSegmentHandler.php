<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation\Handler;

use App\AdherentMessage\StaticSegmentInterface;
use App\Entity\Adherent;
use App\Mailchimp\Exception\StaticSegmentIdMissingException;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use App\Mailchimp\Synchronisation\Command\RemoveAdherentFromStaticSegmentCommand;
use App\Mailchimp\Synchronisation\Command\UpdateStaticSegmentCommandInterface;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class UpdateStaticSegmentHandler
{
    private $mailchimpManager;
    private $adherentRepository;
    private $entityManager;

    public function __construct(
        Manager $mailchimpManager,
        AdherentRepository $adherentRepository,
        ObjectManager $entityManager,
    ) {
        $this->mailchimpManager = $mailchimpManager;
        $this->adherentRepository = $adherentRepository;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateStaticSegmentCommandInterface $command): void
    {
        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid($command->getAdherentUuid()->toString());

        /** @var StaticSegmentInterface $object */
        $object = $this->entityManager
            ->getRepository($command->getEntityClass())
            ->findOneByUuid($command->getObjectUuid()->toString())
        ;

        if (!$adherent || !$object) {
            return;
        }

        $this->entityManager->refresh($adherent);
        $this->entityManager->refresh($object);

        if (!$object->getMailchimpId()) {
            throw new StaticSegmentIdMissingException(\sprintf('%s "%s" does not have Mailchimp static segment id', $object->getUuid()->toString(), basename(str_replace('\\', '/', $object::class))));
        }

        if ($command instanceof RemoveAdherentFromStaticSegmentCommand) {
            $this->mailchimpManager->removeMemberFromStaticSegment($object->getMailchimpId(), $adherent->getEmailAddress());
        } elseif ($command instanceof AddAdherentToStaticSegmentCommand) {
            $this->mailchimpManager->addMemberToStaticSegment($object->getMailchimpId(), $adherent->getEmailAddress());
        }

        $this->entityManager->clear();
    }
}
