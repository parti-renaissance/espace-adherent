<?php

namespace AppBundle\Committee;

use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CommitteeCreationConfirmationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailer;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CommitteeFactory $factory,
        ObjectManager $manager,
        MailerService $mailer
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailer = $mailer;
    }

    public function handle(CommitteeCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $committee = $this->factory->createFromCommitteeCreationCommand($command);

        $command->setCommittee($committee);

        $this->manager->persist($committee);
        $this->manager->persist($adherent->superviseCommittee($committee));
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::COMMITTEE_CREATED, new CommitteeWasCreatedEvent($committee, $adherent));

        $message = CommitteeCreationConfirmationMessage::create($adherent, $committee->getCityName());
        $this->mailer->sendMessage($message);
    }
}
