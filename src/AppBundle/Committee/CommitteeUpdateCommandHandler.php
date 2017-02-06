<?php

namespace AppBundle\Committee;

use AppBundle\Address\PostAddressFactory;
use Doctrine\Common\Persistence\ObjectManager;

class CommitteeUpdateCommandHandler
{
    private $addressFactory;
    private $manager;

    public function __construct(ObjectManager $manager, PostAddressFactory $addressFactory)
    {
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
            $this->addressFactory->createFromAddress($command->getAddress())
        );

        $committee->setSocialNetworks(
            $command->facebookPageUrl,
            $command->twitterNickname,
            $command->googlePlusPageUrl
        );

        $this->manager->flush();
    }
}
