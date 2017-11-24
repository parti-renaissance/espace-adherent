<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenProjectApprovalConfirmationMessage;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenProjectManagementAuthority
{
    private $manager;
    private $mailer;
    private $urlGenerator;
    private $producer;
    private $eventDispatcher;

    public function __construct(
        CitizenProjectManager $manager,
        UrlGeneratorInterface $urlGenerator,
        MailerService $mailer,
        ProducerInterface $producer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
        $this->producer = $producer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function approve(CitizenProject $citizenProject): void
    {
        $this->manager->approveCitizenProject($citizenProject);

        $this->eventDispatcher->dispatch(Events::CITIZEN_PROJECT_APPROVE, new CitizenProjectWasApprovedEvent($citizenProject));
    }

    public function refuse(CitizenProject $citizenProject): void
    {
        $this->manager->refuseCitizenProject($citizenProject);
    }
}
