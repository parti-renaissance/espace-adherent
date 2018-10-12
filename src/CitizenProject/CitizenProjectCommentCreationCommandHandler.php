<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\CitizenProjectComment;
use AppBundle\Events;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectCommentCreationCommandHandler
{
    private $manager;
    private $eventDispatcher;

    public function __construct(ObjectManager $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(CitizenProjectCommentCommand $command): void
    {
        $comment = new CitizenProjectComment(
            null,
            $command->getCitizenProject(),
            $command->getAuthor(),
            $command->getContent()
        );

        $this->manager->persist($comment);
        $this->manager->flush();

        $this->eventDispatcher->dispatch(
            Events::CITIZEN_PROJECT_COMMENT_CREATED,
            new CitizenProjectCommentEvent($command->getCitizenProject(), $comment, $command->isSendMail())
        );
    }
}
