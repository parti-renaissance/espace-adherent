<?php

namespace App\Membership;

use App\AppCodeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message;
use App\Mailer\Message\Renaissance\AdhesionAlreadyAdherentMessage;
use App\Mailer\Message\Renaissance\AdhesionAlreadySympathizerMessage;
use App\Mailer\Message\Renaissance\RenaissanceAdherentAccountCreatedMessage;
use App\OAuth\App\AuthAppUrlManager;
use App\OAuth\CallbackManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class MembershipNotifier implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly CallbackManager $callbackManager,
        private readonly MailerService $transactionalMailer,
        private readonly EntityManagerInterface $manager,
        private readonly AuthAppUrlManager $appUrlManager,
        private readonly LoginLinkHandlerInterface $linkHandler,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    public function sendMembershipAnniversaryReminder(Adherent $adherent): bool
    {
        return $this->transactionalMailer->sendMessage(Message\Renaissance\RenaissanceMembershipAnniversaryMessage::create(
            $adherent,
            $this->createMagicLink($adherent)
        ));
    }

    public function sendEmailReminder(Adherent $adherent): bool
    {
        return $this->transactionalMailer->sendMessage(Message\Renaissance\AdherentMembershipReminderMessage::create(
            $adherent,
            $this->createMagicLink($adherent)
        ));
    }

    public function sendConfirmationJoinMessage(Adherent $adherent, bool $renew): void
    {
        if ($renew) {
            $this->transactionalMailer->sendMessage(Message\Renaissance\RenaissanceReAdhesionConfirmationMessage::createFromAdherent($adherent));
        } else {
            $this->transactionalMailer->sendMessage(Message\Renaissance\RenaissanceAdherentAccountConfirmationMessage::createFromAdherent($adherent));
        }
    }

    public function sendUnregistrationMessage(Adherent $adherent): void
    {
        $this->transactionalMailer->sendMessage(Message\Renaissance\RenaissanceAdherentTerminateMembershipMessage::createFromAdherent($adherent));
    }

    public function sendAccountCreatedEmail(Adherent $adherent): void
    {
        $token = AdherentResetPasswordToken::generate($adherent, '+30 days');
        $message = RenaissanceAdherentAccountCreatedMessage::create(
            $adherent,
            $this->appUrlManager->getUrlGenerator($adherent->getSource())->generateCreatePasswordLink($adherent, $token, ['is_creation' => true])
        );

        $this->manager->persist($token);
        $this->manager->flush();

        $this->transactionalMailer->sendMessage($message);
    }

    public function sendConnexionDetailsMessage(Adherent $adherent, ?string $appCode = null): void
    {
        $url = $this->linkHandler->createLoginLink($adherent, appCode: $appCode);

        if ($adherent->isRenaissanceAdherent()) {
            $this->transactionalMailer->sendMessage(AdhesionAlreadyAdherentMessage::create(
                $adherent,
                $url,
                $this->urlGenerator->generate('app_forgot_password', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $url.'&_target_path='.$this->urlGenerator->generate('app_adhesion_index'),
            ));

            return;
        }

        if ($adherent->isRenaissanceSympathizer()) {
            $url .= '&_target_path='.$this->urlGenerator->generate('app_adhesion_index');
        }

        $this->transactionalMailer->sendMessage(AdhesionAlreadySympathizerMessage::create($adherent, $url));
    }

    private function createMagicLink(Adherent $adherent): string
    {
        return $this->loginLinkHandler->createLoginLink(
            $adherent,
            null,
            60 * 60 * 48,
            AppCodeEnum::RENAISSANCE,
            $this->urlGenerator->generate('app_adhesion_index', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }
}
