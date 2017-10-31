<?php

namespace AppBundle\Group;

use AppBundle\Events;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\GroupCreationConfirmationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GroupCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailjet;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        GroupFactory $factory,
        ObjectManager $manager,
        MailjetService $mailjet
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailjet = $mailjet;
    }

    public function handle(GroupCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $group = $this->factory->createFromGroupCreationCommand($command);

        $command->setGroup($group);

        $this->manager->persist($group);
        $this->manager->persist($adherent->followGroup($group));
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::GROUP_CREATED, new GroupWasCreatedEvent($group, $adherent));

        $this->mailjet->sendMessage(GroupCreationConfirmationMessage::create($adherent));
    }
}
