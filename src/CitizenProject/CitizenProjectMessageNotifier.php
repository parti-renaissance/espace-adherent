<?php

namespace AppBundle\CitizenProject;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\CitizenProjectApprovalConfirmationMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationConfirmationMessage;
use AppBundle\Mailer\Message\CitizenProjectCreationNotificationMessage;
use AppBundle\Mailer\Message\CitizenProjectRequestCommitteeSupportMessage;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

class CitizenProjectMessageNotifier implements EventSubscriberInterface
{
    const RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN = 100;
    const NOTIFICATION_PER_PAGE = MailerService::PAYLOAD_MAXSIZE;

    private $creationNotificationProducer;
    private $manager;
    private $mailer;
    private $committeeManager;
    private $router;

    public function __construct(
        ProducerInterface $creationNotificationProducer,
        CitizenProjectManager $manager,
        MailerService $mailer,
        CommitteeManager $committeeManager,
        RouterInterface $router
    ) {
        $this->creationNotificationProducer = $creationNotificationProducer;
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->committeeManager = $committeeManager;
        $this->router = $router;
    }

    public function onCitizenProjectApprove(CitizenProjectWasApprovedEvent $event): void
    {
        $this->scheduleCreationNotification($event->getCitizenProject());
        $this->sendCreatorApprove($event->getCitizenProject());
        $this->sendAskCommitteeSupport($event->getCitizenProject());
    }

    public function onCitizenProjectCreation(CitizenProjectWasCreatedEvent $event): void
    {
        $this->sendCreatorConfirmationCreation($event->getCreator(), $event->getCitizenProject());
    }

    public function sendAdherentNotificationCreation(Adherent $adherent, CitizenProject $citizenProject, Adherent $creator): void
    {
        $this->mailer->sendMessage(CitizenProjectCreationNotificationMessage::create($adherent, $citizenProject, $creator));
    }

    private function sendCreatorApprove(CitizenProject $citizenProject): void
    {
        $this->mailer->sendMessage(CitizenProjectApprovalConfirmationMessage::create($citizenProject));
    }

    private function sendCreatorConfirmationCreation(Adherent $creator, CitizenProject $citizenProject): void
    {
        $this->mailer->sendMessage(CitizenProjectCreationConfirmationMessage::create($creator, $citizenProject));
    }

    private function scheduleCreationNotification(CitizenProject $citizenProject): void
    {
        $this->creationNotificationProducer->publish(\GuzzleHttp\json_encode([
            'uuid' => $citizenProject->getUuid()->toString(),
            'offset' => 0,
        ]));
    }

    private function sendAskCommitteeSupport(CitizenProject $citizenProject): void
    {
        foreach ($citizenProject->getPendingCommitteeSupports() as $committeeSupport) {
            if (!$committeeHost = $this->committeeManager->getCommitteeSupervisor($committeeSupport->getCommittee())) {
                continue;
            }

            $this->mailer->sendMessage(
                CitizenProjectRequestCommitteeSupportMessage::create(
                    $citizenProject,
                    $committeeHost,
                    $this->router->generate('app_citizen_project_committee_support', [
                        'slug' => $citizenProject->getSlug(),
                    ])
                )
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CITIZEN_PROJECT_CREATED => ['onCitizenProjectCreation', -128],
            Events::CITIZEN_PROJECT_APPROVED => ['onCitizenProjectApprove', -128],
        ];
    }
}
