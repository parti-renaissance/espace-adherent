<?php

namespace App\Committee\CommandHandler;

use App\Address\PostAddressFactory;
use App\Committee\DTO\CommitteeCommand;
use App\Committee\Event\CommitteeEvent;
use App\Events;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeUpdateCommandHandler
{
    private $dispatcher;
    private $addressFactory;
    private $manager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ObjectManager $manager,
        PostAddressFactory $addressFactory,
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
    }

    public function handle(CommitteeCommand $command)
    {
        if (!$committee = $command->getCommittee()) {
            throw new \RuntimeException('A Committee instance is required.');
        }

        $committee->update(
            $command->name,
            $command->description,
            $this->addressFactory->createFromAddress($command->getAddress(), true)
        );

        $committee->setSocialNetworks($command->facebookPageUrl, $command->twitterNickname);

        $this->manager->persist($committee);
        $this->manager->flush();

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
    }

    public function handleForPreApprove(CommitteeCommand $command)
    {
        if (!$committee = $command->getCommittee()) {
            throw new \RuntimeException('A Committee instance is required.');
        }

        $committee->update(
            $command->name,
            $command->description,
            $this->addressFactory->createFromAddress($command->getAddress(), true)
        );

        if ($adherentPSF = $command->getProvisionalSupervisorFemale()) {
            $committee->updateProvisionalSupervisor($adherentPSF);
        }

        if ($adherentPSM = $command->getProvisionalSupervisorMale()) {
            $committee->updateProvisionalSupervisor($adherentPSM);
        }

        $committee->preApproved();

        $this->manager->flush();

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
    }
}
