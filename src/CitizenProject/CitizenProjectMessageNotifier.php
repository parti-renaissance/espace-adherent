<?php

namespace AppBundle\CitizenProject;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenProjectApprovalConfirmationMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationConfirmationMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationNotificationMessage;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenProjectMessageNotifier implements EventSubscriberInterface
{
    const RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN = 100;
    const NOTIFICATION_PER_PAGE = MailerService::PAYLOAD_MAXSIZE;

    private $creationNotificationProducer;
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        ProducerInterface $creationNotificationProducer,
        CitizenProjectManager $manager,
        MailerService $mailer,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->creationNotificationProducer = $creationNotificationProducer;
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function onCitizenProjectApprove(CitizenProjectWasApprovedEvent $event): void
    {
        $this->scheduleCreationNotification($event->getCitizenProject());
        $this->sendCreatorApprove($this->manager->getCitizenProjectCreator($event->getCitizenProject()), $event->getCitizenProject());
    }

    public function onCitizenProjectCreation(CitizenProjectWasCreatedEvent $event): void
    {
        $this->sendCreatorConfirmationCreation($event->getCreator());
    }

    public function sendAdherentNotificationCreation(Adherent $adherent): void
    {
        /*
         * si l'adhérent n'a pas de notification activé => return
         * si l'adhérent a activé toutes les notifications => envoie direct
         * si l'adherent est à une distance de notification inférieur ou égale du PC
         */
        $this->mailer->sendMessage(CitizenProjectCreationNotificationMessage::create($adherent));
    }

    public function sendCreatorApprove(Adherent $creator, CitizenProject $citizenProject): void
    {
        $this->mailer->sendMessage(CitizenProjectApprovalConfirmationMessage::create(
            $creator,
            $citizenProject->getCityName(),
            $this->urlGenerator->generate('app_citizen_project_show', ['slug' => $citizenProject->getSlug()])
        ));
    }

    public function sendCreatorConfirmationCreation(Adherent $creator): void
    {
        $this->mailer->sendMessage(CitizenProjectCreationConfirmationMessage::create($creator));
    }

    private function scheduleCreationNotification(CitizenProject $citizenProject): void
    {
        $this->creationNotificationProducer->publish(\GuzzleHttp\json_encode([
            'uuid' => $citizenProject->getUuid()->toString(),
            'offset' => 0
        ]));
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CITIZEN_PROJECT_CREATED => ['onCitizenProjectCreation', -128],
            Events::CITIZEN_PROJECT_APPROVE => ['onCitizenProjectApprove', -128],
        ];
    }
}