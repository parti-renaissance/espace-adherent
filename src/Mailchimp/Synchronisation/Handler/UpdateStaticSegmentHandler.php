<?php

namespace AppBundle\Mailchimp\Synchronisation\Handler;

use AppBundle\AdherentMessage\StaticSegmentInterface;
use AppBundle\Entity\Adherent;
use AppBundle\Mailchimp\Exception\StaticSegmentIdMissingException;
use AppBundle\Mailchimp\Manager;
use AppBundle\Mailchimp\Synchronisation\Command\AddAdherentToStaticSegmentCommand;
use AppBundle\Mailchimp\Synchronisation\Command\RemoveAdherentFromStaticSegmentCommand;
use AppBundle\Mailchimp\Synchronisation\Command\UpdateStaticSegmentCommandInterface;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateStaticSegmentHandler implements MessageHandlerInterface
{
    private $mailchimpManager;
    private $adherentRepository;
    private $entityManager;

    public function __construct(
        Manager $mailchimpManager,
        AdherentRepository $adherentRepository,
        ObjectManager $entityManager
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
            throw new StaticSegmentIdMissingException(
                sprintf(
                    '%s "%s" does not have Mailchimp static segment id',
                    $object->getUuid()->toString(),
                    basename(str_replace('\\', '/', \get_class($object)))
                )
            );
        }

        if ($command instanceof RemoveAdherentFromStaticSegmentCommand) {
            $this->mailchimpManager->removeMemberFromStaticSegment($object->getMailchimpId(), $adherent->getEmailAddress());
        } elseif ($command instanceof AddAdherentToStaticSegmentCommand) {
            $this->mailchimpManager->addMemberToStaticSegment($object->getMailchimpId(), $adherent->getEmailAddress());
        }

        $this->entityManager->clear();
    }
}
