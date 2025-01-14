<?php

namespace App\Committee\CommandHandler;

use App\Address\PostAddressFactory;
use App\Committee\DTO\CommitteeCommand;
use App\Committee\Event\EditCommitteeEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CommitteeUpdateCommandHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $manager,
        private readonly PostAddressFactory $addressFactory,
    ) {
    }

    public function handle(CommitteeCommand $command): void
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

        $this->dispatcher->dispatch(new EditCommitteeEvent($committee));
    }
}
