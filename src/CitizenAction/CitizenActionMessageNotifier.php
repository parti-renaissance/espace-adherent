<?php

namespace AppBundle\CitizenAction;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Events;
use AppBundle\Mail\Transactional\CitizenActionCancellationMail;
use AppBundle\Mail\Transactional\CitizenActionNotificationMail;
use AppBundle\Repository\EventRegistrationRepository;
use EnMarche\MailerBundle\MailPost\MailPostInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CitizenActionMessageNotifier implements EventSubscriberInterface
{
    private $mailPost;
    private $urlGenerator;
    private $citizenProjectManager;
    private $registrationRepository;

    public function __construct(
        MailPostInterface $mailPost,
        UrlGeneratorInterface $urlGenerator,
        CitizenProjectManager $citizenProjectManager,
        EventRegistrationRepository $registrationRepository
    ) {
        $this->mailPost = $mailPost;
        $this->urlGenerator = $urlGenerator;
        $this->citizenProjectManager = $citizenProjectManager;
        $this->registrationRepository = $registrationRepository;
    }

    public function sendCreationEmail(CitizenActionEvent $citizenActionEvent): void
    {
        $citizenAction = $citizenActionEvent->getCitizenAction();

        $followers = $this->citizenProjectManager->getOptinCitizenProjectFollowers($citizenAction->getCitizenProject())->toArray();

        $this->mailPost->address(
            CitizenActionNotificationMail::class,
            CitizenActionNotificationMail::createRecipientsFrom($followers),
            CitizenActionNotificationMail::createRecipientFromAdherent($citizenAction->getOrganizer()),
            CitizenActionNotificationMail::createTemplateVarsFrom(
                $citizenAction,
                $this->generateUrl('app_citizen_action_attend', ['slug' => $citizenAction->getSlug()])
            ),
            CitizenActionNotificationMail::SUBJECT
        );
    }

    public function sendCancellationEmail(CitizenActionEvent $citizenActionEvent): void
    {
        $citizenAction = $citizenActionEvent->getCitizenAction();
        if (!$citizenAction->isCancelled()) {
            return;
        }

        $subscriptions = $this->registrationRepository->findByEvent($citizenAction);

        if (\count($subscriptions) > 0) {
            $this->mailPost->address(
                CitizenActionCancellationMail::class,
                CitizenActionCancellationMail::createRecipientsFrom($subscriptions),
                CitizenActionCancellationMail::createRecipientFromAdherent($citizenActionEvent->getAuthor()),
                CitizenActionCancellationMail::createTemplateVarsFrom(
                    $citizenAction,
                    $this->generateUrl('app_search_events')
                ),
                CitizenActionCancellationMail::SUBJECT
            );
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CITIZEN_ACTION_CREATED => ['sendCreationEmail'],
            Events::CITIZEN_ACTION_CANCELLED => ['sendCancellationEmail'],
        ];
    }

    private function generateUrl(string $route, array $params = []): string
    {
        return $this->urlGenerator->generate($route, $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
