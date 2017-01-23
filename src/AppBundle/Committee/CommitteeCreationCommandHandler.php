<?php

namespace AppBundle\Committee;

use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeCreationConfirmationMessage;
use Doctrine\Common\Persistence\ObjectManager;

class CommitteeCreationCommandHandler
{
    private $factory;
    private $manager;
    private $mailjet;

    public function __construct(CommitteeFactory $factory, ObjectManager $manager, MailjetService $mailjet)
    {
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailjet = $mailjet;
    }

    public function handle(CommitteeCreationCommand $command)
    {
        $committee = $this->factory->createFromCommitteeCreationCommand($command);

        $command->setCommittee($committee);

        $this->manager->persist($committee);
        $this->manager->flush();

        $message = CommitteeCreationConfirmationMessage::create($command->getAdherent(), $command->getCityName());
        $this->mailjet->sendMessage($message);
    }
}
