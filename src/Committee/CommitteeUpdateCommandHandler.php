<?php

namespace AppBundle\Committee;

use AppBundle\Address\PostAddressFactory;
use AppBundle\Events;
use AppBundle\Referent\ReferentTagManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommitteeUpdateCommandHandler
{
    private $dispatcher;
    private $addressFactory;
    private $manager;
    private $photoManager;
    private $referentTagManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ObjectManager $manager,
        PostAddressFactory $addressFactory,
        PhotoManager $photoManager,
        ReferentTagManager $referentTagManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
        $this->photoManager = $photoManager;
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
            $this->addressFactory->createFromAddress($command->getAddress()),
            $command->getPhone()
        );

        // Uploads an ID photo
        $this->photoManager->addPhotoFromCommand($command, $committee);

        $committee->setSocialNetworks(
            $command->facebookPageUrl,
            $command->twitterNickname
        );

        $this->referentTagManager->assignReferentLocalTags($committee);

        $this->manager->persist($committee);
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::COMMITTEE_UPDATED, new CommitteeEvent($committee));
    }
}
