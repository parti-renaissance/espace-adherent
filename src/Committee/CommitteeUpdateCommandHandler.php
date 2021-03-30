<?php

namespace App\Committee;

use App\Address\PostAddressFactory;
use App\Events;
use App\Referent\ReferentTagManager;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeUpdateCommandHandler
{
    private $dispatcher;
    private $addressFactory;
    private $manager;
    private $referentTagManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ObjectManager $manager,
        PostAddressFactory $addressFactory,
        ReferentTagManager $referentTagManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
        $this->referentTagManager = $referentTagManager;
    }

    public function handle(CommitteeCommand $command)
    {
        if (!$committee = $command->getCommittee()) {
            throw new \RuntimeException('A Committee instance is required.');
        }

        $committee->update(
            $command->name,
            $command->description,
            $this->addressFactory->createFromAddress($command->getAddress())
        );

        $committee->setSocialNetworks($command->facebookPageUrl, $command->twitterNickname);

        $this->referentTagManager->assignReferentLocalTags($committee);

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
            $this->addressFactory->createFromAddress($command->getAddress())
        );

        if ($adherentPSF = $command->getProvisionalSupervisorFemale()) {
            $committee->updateProvisionalSupervisor($adherentPSF);
        }

        if ($adherentPSM = $command->getProvisionalSupervisorMale()) {
            $committee->updateProvisionalSupervisor($adherentPSM);
        }

        $this->referentTagManager->assignReferentLocalTags($committee);

        $committee->preApproved();

        $this->manager->flush();

        $this->dispatcher->dispatch(new CommitteeEvent($committee), Events::COMMITTEE_UPDATED);
    }
}
