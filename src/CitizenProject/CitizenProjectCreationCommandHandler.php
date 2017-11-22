<?php

namespace AppBundle\CitizenProject;

use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenProjectCreationCommandHandler
{
    private $dispatcher;
    private $factory;
    private $manager;
    private $mailer;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        CitizenProjectFactory $factory,
        ObjectManager $manager,
        MailerService $mailer
    ) {
        $this->dispatcher = $dispatcher;
        $this->factory = $factory;
        $this->manager = $manager;
        $this->mailer = $mailer;
    }

    public function handle(CitizenProjectCreationCommand $command): void
    {
        $adherent = $command->getAdherent();
        $citizenProject = $this->factory->createFromCitizenProjectCreationCommand($command);

        $command->setCitizenProject($citizenProject);

        $this->manager->persist($citizenProject);
        $this->manager->persist($adherent->followCitizenProject($citizenProject));
        $this->manager->flush();

        $this->dispatcher->dispatch(Events::CITIZEN_PROJECT_CREATED, new CitizenProjectWasCreatedEvent($citizenProject, $adherent));
    }
}
