<?php

namespace App\CitizenProject;

use App\Committee\CommitteeManager;
use App\Coordinator\Filter\CitizenProjectFilter;
use App\Entity\Adherent;
use App\Entity\CitizenProject;
use App\Events;
use App\Mailer\MailerService;
use App\Mailer\Message\CitizenProjectApprovalConfirmationMessage;
use App\Mailer\Message\CitizenProjectCreationConfirmationMessage;
use App\Mailer\Message\CitizenProjectCreationCoordinatorNotificationMessage;
use App\Mailer\Message\CitizenProjectCreationNotificationMessage;
use App\Mailer\Message\CitizenProjectNewFollowerMessage;
use App\Mailer\Message\CitizenProjectRequestCommitteeSupportMessage;
use App\Mailer\Message\TurnkeyProjectApprovalConfirmationMessage;
use App\Repository\AdherentRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CitizenProjectMessageNotifier implements EventSubscriberInterface
{
    private $manager;
    private $mailer;
    private $committeeManager;
    private $router;
    private $adherentRepository;

    public function __construct(
        AdherentRepository $adherentRepository,
        CitizenProjectManager $manager,
        MailerService $mailer,
        CommitteeManager $committeeManager,
        RouterInterface $router
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->committeeManager = $committeeManager;
        $this->router = $router;
    }

    public function onCitizenProjectApprove(CitizenProjectWasApprovedEvent $event): void
    {
        $this->sendCreatorApprove($event->getCitizenProject());
        $this->sendAskCommitteeSupport($event->getCitizenProject());
    }

    public function onCitizenProjectCreation(CitizenProjectWasCreatedEvent $event): void
    {
        $creator = $event->getCreator();
        $citizenProject = $event->getCitizenProject();

        $this->sendCreatorCreationConfirmation($creator, $citizenProject);
        $this->sendCoordinatorCreationValidation($creator, $citizenProject);
    }

    public function onCitizenProjectFollowerAdded(CitizenProjectFollowerChangeEvent $followerAddedEvent): void
    {
        if (!$hosts = $this->manager->getCitizenProjectAdministrators($followerAddedEvent->getCitizenProject())->toArray()) {
            return;
        }

        $this->mailer->sendMessage(CitizenProjectNewFollowerMessage::create(
            $followerAddedEvent->getCitizenProject(),
            $hosts,
            $followerAddedEvent->getFollower()
        ));
    }

    public function sendAdherentNotificationCreation(
        Adherent $adherent,
        CitizenProject $citizenProject,
        Adherent $creator
    ): void {
        $this->mailer->sendMessage(CitizenProjectCreationNotificationMessage::create($adherent, $citizenProject, $creator));
    }

    private function sendCreatorApprove(CitizenProject $citizenProject): void
    {
        $this->manager->injectCitizenProjectCreator([$citizenProject]);

        if ($citizenProject->isFromTurnkeyProject()) {
            $message = TurnkeyProjectApprovalConfirmationMessage::create(
                $citizenProject,
                $this->generateUrl('app_citizen_project_show', [
                    'slug' => $citizenProject->getSlug(),
                    '_fragment' => 'citizen-project-files',
                ])
            );
        } else {
            $message = CitizenProjectApprovalConfirmationMessage::create($citizenProject);
        }

        $this->mailer->sendMessage($message);
    }

    private function sendCreatorCreationConfirmation(Adherent $creator, CitizenProject $citizenProject): void
    {
        $this->mailer->sendMessage(CitizenProjectCreationConfirmationMessage::create(
            $creator,
            $citizenProject,
            $this->generateUrl('app_citizen_project_show', [
                'slug' => $citizenProject->getSlug(),
            ])
        ));
    }

    private function sendCoordinatorCreationValidation(Adherent $creator, CitizenProject $citizenProject): void
    {
        $coordinators = $this->adherentRepository->findCoordinatorsByCitizenProject($citizenProject);

        foreach ($coordinators as $coordinator) {
            $this->mailer->sendMessage(
                CitizenProjectCreationCoordinatorNotificationMessage::create(
                    $coordinator,
                    $citizenProject,
                    $creator,
                    $this->generateUrl('app_coordinator_citizen_project', [
                        CitizenProjectFilter::PARAMETER_STATUS => CitizenProject::PENDING,
                    ])
                )
            );
        }
    }

    private function sendAskCommitteeSupport(CitizenProject $citizenProject): void
    {
        $this->manager->injectCitizenProjectCreator([$citizenProject]);
        foreach ($citizenProject->getPendingCommitteeSupports() as $committeeSupport) {
            if (!$committeeSupervisor = $this->committeeManager->getCommitteeSupervisor($committeeSupport->getCommittee())) {
                continue;
            }

            $this->mailer->sendMessage(
                CitizenProjectRequestCommitteeSupportMessage::create(
                    $citizenProject,
                    $committeeSupervisor,
                    $this->generateUrl('app_citizen_project_committee_support', [
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
            Events::CITIZEN_PROJECT_FOLLOWER_ADDED => ['onCitizenProjectFollowerAdded', -128],
        ];
    }

    private function generateUrl(string $name, array $parameters = []): string
    {
        return $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
