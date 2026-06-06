<?php

declare(strict_types=1);

namespace App\Membership\Signup\Listener;

use App\AppCodeEnum;
use App\Mailer\MailerService;
use App\Mailer\Message\Campaign\CampaignWelcomeMessage;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Security\Http\LoginLink\LoginLinkHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendCampaignWelcomeEmailListener implements EventSubscriberInterface
{
    private const MAGIC_LINK_LIFETIME = 900;

    public function __construct(
        private readonly MailerService $mailer,
        private readonly LoginLinkHandler $loginLinkHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_VALIDATED => 'onUserValidated',
        ];
    }

    public function onUserValidated(UserEvent $event): void
    {
        $adherent = $event->getAdherent();

        if (!$adherent->signupAccount || $adherent->isDisabled()) {
            return;
        }

        $magicLink = $this->loginLinkHandler
            ->createLoginLink($adherent, lifetime: self::MAGIC_LINK_LIFETIME, appCode: AppCodeEnum::CAMPAIGN)
            ->getUrl()
        ;

        $this->mailer->sendMessage(CampaignWelcomeMessage::createFromAdherent($adherent, $magicLink));
    }
}
