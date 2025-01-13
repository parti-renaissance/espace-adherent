<?php

namespace App\Committee\CommandHandler;

use App\Committee\CommitteeFactory;
use App\Committee\DTO\CommitteeCreationCommand;
use App\Committee\Event\EditCommitteeEvent;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeCreationConfirmationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $entityManager;
    private $mailer;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CommitteeFactory $factory,
        EntityManagerInterface $entityManager,
        MailerService $transactionalMailer,
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->entityManager = $entityManager;
        $this->mailer = $transactionalMailer;
    }

    public function handle(CommitteeCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $committee = $this->factory->createFromCommitteeCreationCommand($command, $adherent);
        $committee->updateProvisionalSupervisor($adherent);
        $command->setCommittee($committee);
        if ($command->getPhone()) {
            $adherent->setPhone($command->getPhone());
        }

        $this->entityManager->persist($committee);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new EditCommitteeEvent($committee));

        $message = CommitteeCreationConfirmationMessage::create($adherent, $committee->getCityName());
        $this->mailer->sendMessage($message);
    }
}
