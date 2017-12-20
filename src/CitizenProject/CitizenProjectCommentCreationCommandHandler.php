<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\CitizenProjectComment;
use Doctrine\Common\Persistence\ObjectManager;

class CitizenProjectCommentCreationCommandHandler
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
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
    }
}
