<?php

namespace AppBundle\Committee;

use AppBundle\Events;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\CommitteeCreationConfirmationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailjet;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CommitteeFactory $factory,
        ObjectManager $manager,
        MailjetService $mailjet
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailjet = $mailjet;
    }

    public function handle(CommitteeCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $committee = $this->factory->createFromCommitteeCreationCommand($command);

        $command->setCommittee($committee);

        $this->manager->persist($committee);
        $this->manager->persist($adherent->followCommittee($committee));
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::COMMITTEE_CREATED, new CommitteeWasCreatedEvent($committee, $adherent));

        $message = CommitteeCreationConfirmationMessage::create($adherent, $committee->getCityName());
        $this->mailjet->sendMessage($message);
    }
}
