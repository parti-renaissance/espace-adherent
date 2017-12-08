<?php

namespace AppBundle\CitizenAction;

use AppBundle\Entity\CitizenAction;
use AppBundle\Event\EventFactory;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenActionCreationConfirmationMessage;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenActionCommandHandler
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

    public function handle(CitizenActionCommand $command): CitizenAction
    {
        $action = $this->factory->createFromCitizenActionCommand($command);

        $this->manager->persist($action);
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_ACTION_CREATED, new CitizenActionEvent($action));

        $this->mailer->sendMessage(CitizenActionCreationConfirmationMessage::create($action));

        return $action;
    }

    public function handleUpdate(CitizenActionCommand $command, CitizenAction $action): void
    {
        $this->factory->updateFromCitizenActionCommand($command, $action);

        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_ACTION_UPDATED, new CitizenActionEvent($action));
    }
}
