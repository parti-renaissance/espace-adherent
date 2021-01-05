<?php

namespace App\Committee;

use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeCreationConfirmationMessage;
use App\Referent\ReferentTagManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailer;
    private $referentTagManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CommitteeFactory $factory,
        CommitteeManager $manager,
        MailerService $transactionalMailer,
        ReferentTagManager $referentTagManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailer = $transactionalMailer;
        $this->referentTagManager = $referentTagManager;
    }

    public function handle(CommitteeCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $committee = $this->factory->createFromCommitteeCreationCommand($command);

        $this->referentTagManager->assignReferentLocalTags($committee);

        $command->setCommittee($committee);
        $adherent->setPhone($command->getPhone());

        $this->manager->followCommittee($adherent, $committee);

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_CREATED);

        $message = CommitteeCreationConfirmationMessage::create($adherent, $committee->getCityName());
        $this->mailer->sendMessage($message);
    }
}
