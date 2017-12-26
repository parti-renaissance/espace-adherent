<?php

namespace AppBundle\CitizenInitiative;

use AppBundle\Entity\CitizenInitiative;
use AppBundle\Event\EventFactory;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenInitiativeCreationConfirmationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenInitiativeCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailer;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EventFactory $factory,
        ObjectManager $manager,
        MailerService $mailer
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->factory = $factory;
        $this->mailer = $mailer;
    }

    public function handle(CitizenInitiativeCommand $command): void
    {
        $command->setCitizenInitiative($initiative = $this->factory->createFromCitizenInitiativeCommand($command));

        $this->manager->persist($initiative);
        $this->manager->flush();

        $initiativeEvent = new CitizenInitiativeCreatedEvent($command->getAuthor(), $initiative);

        $this->dispatcher->dispatch(Events::CITIZEN_INITIATIVE_CREATED, $initiativeEvent);

        $this->mailer->sendMessage(CitizenInitiativeCreationConfirmationMessage::create($initiativeEvent));
    }

    public function handleUpdate(CitizenInitiative $initiative, CitizenInitiativeCommand $command)
    {
        $this->factory->updateFromCitizenInitiativeCommand($initiative, $command);

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_INITIATIVE_UPDATED, new CitizenInitiativeUpdatedEvent(
            $command->getAuthor(),
            $initiative
        ));

        return $initiative;
    }

    public function handleCancel(CitizenInitiative $initiative, CitizenInitiativeCommand $command)
    {
        $initiative->cancel();

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_INITIATIVE_CANCELLED, new CitizenInitiativeCancelledEvent(
            $command->getAuthor(),
            $initiative
        ));

        return $initiative;
    }
}
