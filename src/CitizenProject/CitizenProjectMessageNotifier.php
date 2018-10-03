<?php

namespace AppBundle\CitizenProject;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Coordinator\Filter\CitizenProjectFilter;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Events;
use AppBundle\Mail\Transactional\CitizenProjectApprovalConfirmationMail;
use AppBundle\Mail\Transactional\CitizenProjectCommentMail;
use AppBundle\Mail\Transactional\CitizenProjectCreationConfirmationMail;
use AppBundle\Mail\Transactional\CitizenProjectCreationCoordinatorNotificationMail;
use AppBundle\Mail\Transactional\CitizenProjectCreationNotificationMail;
use AppBundle\Mail\Transactional\CitizenProjectNewFollowerMail;
use AppBundle\Mail\Transactional\CitizenProjectRequestCommitteeSupportMail;
use AppBundle\Mail\Transactional\TurnkeyProjectApprovalConfirmationMail;
use AppBundle\Mailer\MailerService;
use AppBundle\Repository\AdherentRepository;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class CitizenProjectMessageNotifier implements EventSubscriberInterface
{
    public const RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN = 100;
    public const NOTIFICATION_PER_PAGE = MailerService::PAYLOAD_MAXSIZE;

    private $manager;
    private $mailPost;
    private $committeeManager;
    private $router;
    private $adherentRepository;

    public function __construct(
        AdherentRepository $adherentRepository,
        CitizenProjectManager $manager,
        MailPostInterface $mailPost,
        CommitteeManager $committeeManager,
        RouterInterface $router
    ) {
        $this->adherentRepository = $adherentRepository;
        $this->manager = $manager;
        $this->mailPost = $mailPost;
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

    public function onCitizenProjectFollowerAdded(CitizenProjectFollowerAddedEvent $followerAddedEvent): void
    {
        if (!$hosts = $this->manager->getCitizenProjectAdministrators($followerAddedEvent->getCitizenProject())->toArray()) {
            return;
        }

        $this->mailPost->address(
            CitizenProjectNewFollowerMail::class,
            CitizenProjectNewFollowerMail::createRecipientsFrom($hosts),
            CitizenProjectNewFollowerMail::createRecipientFromAdherent($followerAddedEvent->getNewFollower()),
            CitizenProjectNewFollowerMail::createTemplateVars($followerAddedEvent->getCitizenProject(), $followerAddedEvent->getNewFollower()),
            CitizenProjectNewFollowerMail::SUBJECT
        );
    }

    public function sendCommentCreatedEmail(CitizenProjectCommentEvent $commentCreatedEvent): void
    {
        if ($commentCreatedEvent->isSendMail()) {
            $author = $commentCreatedEvent->getComment()->getAuthor();

            $this->mailPost->address(
                CitizenProjectCommentMail::class,
                CitizenProjectCommentMail::createRecipientsFrom(
                    $this->manager->getCitizenProjectMembers($commentCreatedEvent->getCitizenProject())->toArray()
                ),
                CitizenProjectCommentMail::createRecipientFromAdherent($author),
                CitizenProjectCommentMail::createTemplateVars($author, $commentCreatedEvent->getComment()),
                CitizenProjectCommentMail::SUBJECT,
                CitizenProjectCommentMail::createSender($author)
            );
        }
    }

    public function sendAdherentNotificationCreation(array $adherents, CitizenProject $citizenProject, Adherent $creator): void
    {
        $this->mailPost->address(
            CitizenProjectCreationNotificationMail::class,
            CitizenProjectCreationNotificationMail::createRecipientsFrom($adherents),
            null,
            CitizenProjectCreationNotificationMail::createTemplateVars($citizenProject, $creator),
            CitizenProjectCreationNotificationMail::SUBJECT,
            CitizenProjectCreationNotificationMail::createSender()
        );
    }

    private function sendCreatorApprove(CitizenProject $citizenProject): void
    {
        $this->manager->injectCitizenProjectCreator([$citizenProject]);

        if (!$author = $citizenProject->getCreator()) {
            return;
        }

        if ($citizenProject->isFromTurnkeyProject()) {
            $this->mailPost->address(
                TurnkeyProjectApprovalConfirmationMail::class,
                TurnkeyProjectApprovalConfirmationMail::createRecipientFromAdherent($citizenProject->getCreator()),
                TurnkeyProjectApprovalConfirmationMail::createTemplateVarsFrom(
                    $citizenProject,
                    $this->generateUrl('app_citizen_project_show', [
                        'slug' => $citizenProject->getSlug(),
                        '_fragment' => 'citizen-project-files',
                    ])
                ),
                TurnkeyProjectApprovalConfirmationMail::SUBJECT
            );
        } else {
            $this->mailPost->address(
                CitizenProjectApprovalConfirmationMail::class,
                CitizenProjectApprovalConfirmationMail::createRecipientFrom($citizenProject),
                null,
                [],
                CitizenProjectApprovalConfirmationMail::SUBJECT,
                CitizenProjectApprovalConfirmationMail::createSender()
            );
        }
    }

    private function sendCreatorCreationConfirmation(Adherent $creator, CitizenProject $citizenProject): void
    {
        $this->mailPost->address(
            CitizenProjectCreationConfirmationMail::class,
            CitizenProjectCreationConfirmationMail::createRecipientFrom($creator),
            null,
            CitizenProjectCreationConfirmationMail::createTemplateVars(
                $creator->getFirstName(),
                $citizenProject->getName(),
                $this->generateUrl('app_citizen_action_manager_create', [
                    'project_slug' => $citizenProject->getSlug(),
                ])
            ),
            CitizenProjectCreationConfirmationMail::SUBJECT,
            CitizenProjectCreationConfirmationMail::createSender()
        );
    }

    private function sendCoordinatorCreationValidation(Adherent $creator, CitizenProject $citizenProject): void
    {
        $coordinators = $this->adherentRepository->findCoordinatorsByCitizenProject($citizenProject);

        if (!$coordinators->count()) {
            return;
        }

        $this->mailPost->address(
            CitizenProjectCreationCoordinatorNotificationMail::class,
            CitizenProjectCreationCoordinatorNotificationMail::createRecipientsFrom($coordinators->toArray()),
            null,
            CitizenProjectCreationCoordinatorNotificationMail::createTemplateVars(
                $citizenProject,
                $creator,
                $this->generateUrl('app_coordinator_citizen_project', [
                    CitizenProjectFilter::PARAMETER_STATUS => CitizenProject::PENDING,
                ])
            ),
            CitizenProjectCreationCoordinatorNotificationMail::SUBJECT,
            CitizenProjectCreationCoordinatorNotificationMail::createSender()
        );
    }

    private function sendAskCommitteeSupport(CitizenProject $citizenProject): void
    {
        $this->manager->injectCitizenProjectCreator([$citizenProject]);
        foreach ($citizenProject->getPendingCommitteeSupports() as $committeeSupport) {
            if (!$committeeSupervisor = $this->committeeManager->getCommitteeSupervisor($committeeSupport->getCommittee())) {
                continue;
            }

            $this->mailPost->address(
                CitizenProjectRequestCommitteeSupportMail::class,
                CitizenProjectRequestCommitteeSupportMail::createRecipientFromAdherent($committeeSupervisor),
                null,
                CitizenProjectRequestCommitteeSupportMail::createTemplateVars(
                    $citizenProject,
                    $this->generateUrl('app_citizen_project_committee_support', ['slug' => $citizenProject->getSlug()])
                ),
                CitizenProjectRequestCommitteeSupportMail::SUBJECT,
                CitizenProjectRequestCommitteeSupportMail::createSender()
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CITIZEN_PROJECT_CREATED => ['onCitizenProjectCreation', -128],
            Events::CITIZEN_PROJECT_APPROVED => ['onCitizenProjectApprove', -128],
            Events::CITIZEN_PROJECT_FOLLOWER_ADDED => ['onCitizenProjectFollowerAdded', -128],
            Events::CITIZEN_PROJECT_COMMENT_CREATED => ['sendCommentCreatedEmail', -128],
        ];
    }

    private function generateUrl(string $name, array $parameters = []): string
    {
        return $this->router->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
