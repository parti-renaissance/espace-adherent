<?php

namespace App\Mailchimp\Synchronisation\Handler;

use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Manager;
use App\Mailchimp\Synchronisation\Command\UpdateCauseMailchimpIdCommand;
use App\Mailchimp\Synchronisation\Utils\TagBuilder;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateCauseMailchimpIdCommandHandler implements MessageHandlerInterface
{
    private $mailchimpManager;
    private $objectIdMapping;
    private $entityManager;

    public function __construct(
        Manager $mailchimpManager,
        MailchimpObjectIdMapping $objectIdMapping,
        ObjectManager $entityManager
    ) {
        $this->mailchimpManager = $mailchimpManager;
        $this->objectIdMapping = $objectIdMapping;
        $this->entityManager = $entityManager;
    }

    public function __invoke(UpdateCauseMailchimpIdCommand $command): void
    {
        $cause = $command->getCause();

        $this->entityManager->refresh($cause);

        if ($cause->getMailchimpId()) {
            return;
        }

        $id = $this->mailchimpManager->createStaticSegment(
            TagBuilder::createCauseFollowTag($cause->getId()),
            $this->objectIdMapping->getCoalitionsListId()
        );

        if ($id) {
            $cause->setMailchimpId($id);
            $this->entityManager->flush();
        }
    }
}
