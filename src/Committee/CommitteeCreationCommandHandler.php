<?php

namespace App\Committee;

use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\CommitteeCreationConfirmationMessage;
use App\Referent\ReferentTagManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $entityManager;
    private $mailer;
    private $referentTagManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CommitteeFactory $factory,
        EntityManagerInterface $entityManager,
        MailerService $transactionalMailer,
        ReferentTagManager $referentTagManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->entityManager = $entityManager;
        $this->mailer = $transactionalMailer;
        $this->referentTagManager = $referentTagManager;
    }

    public function handle(CommitteeCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $committee = $this->factory->createFromCommitteeCreationCommand($command, $adherent);
        if ($adherent->isReferent()) {
            $committee->preApproved();
            if ($adherentPSF = $command->getProvisionalSupervisorFemale()) {
                $committee->updateProvisionalSupervisor($adherentPSF);
            }

            if ($adherentPSM = $command->getProvisionalSupervisorMale()) {
                $committee->updateProvisionalSupervisor($adherentPSM);
            }
        } else {
            $committee->updateProvisionalSupervisor($adherent);
        }

        $this->referentTagManager->assignReferentLocalTags($committee);

        $command->setCommittee($committee);
        if ($command->getPhone()) {
            $adherent->setPhone($command->getPhone());
        }

        $this->entityManager->persist($committee);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_CREATED);

        $message = CommitteeCreationConfirmationMessage::create($adherent, $committee->getCityName());
        $this->mailer->sendMessage($message);
    }
}
