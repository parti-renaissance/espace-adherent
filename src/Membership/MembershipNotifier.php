<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\AdherentResetPasswordToken;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Mailer\MailerService;
use App\Mailer\Message;
use App\Mailer\Message\Renaissance\AdhesionAlreadyAdherentMessage;
use App\Mailer\Message\Renaissance\AdhesionAlreadySympathizerMessage;
use App\Mailer\Message\Renaissance\RenaissanceAdherentAccountActivationMessage;
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
    ) {
    }

    public function sendEmailValidation(Adherent $adherent, bool $isReminder = false): bool
    {
        $token = AdherentActivationToken::generate($adherent);

        $this->manager->persist($token);
        $this->manager->flush();

        $activationUrl = $this->generateMembershipActivationUrl($adherent, $token);

        if ($isReminder) {
            return $this->transactionalMailer->sendMessage(Message\AdherentAccountActivationReminderMessage::create($adherent, $activationUrl));
        }

        return $this->transactionalMailer->sendMessage(Message\AdherentAccountActivationMessage::create($adherent, $activationUrl));
    }

    public function sendRenaissanceValidationEmail(AdherentRequest $adherentRequest): void
    {
        $activationUrl = $this->callbackManager->generateUrl(
            'app_renaissance_membership_validate',
            [
                'uuid' => $adherentRequest->getUuid()->toString(),
                'token' => $adherentRequest->token->toString(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->transactionalMailer->sendMessage(RenaissanceAdherentAccountActivationMessage::create($adherentRequest, $activationUrl));
    }

    public function sendEmailReminder(Adherent $adherent): bool
    {
        $donationUrl = $this->callbackManager->generateUrl('donation_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->transactionalMailer->sendMessage(Message\AdherentMembershipReminderMessage::create($adherent, $donationUrl));
    }

    public function sendConfirmationJoinMessage(Adherent $adherent): void
    {
        MembershipSourceEnum::RENAISSANCE === $adherent->getSource()
            ? $this->transactionalMailer->sendMessage(Message\Renaissance\RenaissanceAdherentAccountConfirmationMessage::createFromAdherent(
                $adherent,
                $this->callbackManager->generateUrl('app_renaissance_adherent_profile', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->callbackManager->generateUrl('app_renaissance_donation', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->callbackManager->generateUrl('app_my_committee_show_current', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ))
            : $this->transactionalMailer->sendMessage(Message\AdherentAccountConfirmationMessage::createFromAdherent($adherent));
    }

    public function sendReAdhesionConfirmationMessage(Adherent $adherent): void
    {
        $this->transactionalMailer->sendMessage(Message\Renaissance\RenaissanceReAdhesionConfirmationMessage::createFromAdherent(
            $adherent,
            $this->callbackManager->generateUrl('app_renaissance_adherent_profile', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->callbackManager->generateUrl('app_renaissance_donation', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->callbackManager->generateUrl('app_my_committee_show_current', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ));
    }

    public function sendUnregistrationMessage(Adherent $adherent): void
    {
        MembershipSourceEnum::RENAISSANCE === $adherent->getSource()
            ? $this->transactionalMailer->sendMessage(Message\Renaissance\RenaissanceAdherentTerminateMembershipMessage::createFromAdherent($adherent))
            : $this->transactionalMailer->sendMessage(Message\AdherentTerminateMembershipMessage::createFromAdherent($adherent));
    }

    private function generateMembershipActivationUrl(Adherent $adherent, AdherentActivationToken $token): string
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'activation_token' => (string) $token->getValue(),
        ];

        return $this->callbackManager->generateUrl(
            'app_membership_activate',
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
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

    public function sendConnexionDetailsMessage(Adherent $adherent): void
    {
        $url = $this->linkHandler->createLoginLink($adherent);

        if ($adherent->isRenaissanceAdherent()) {
            $this->transactionalMailer->sendMessage(AdhesionAlreadyAdherentMessage::create(
                $adherent,
                $url,
                $this->urlGenerator->generate('app_renaissance_forgot_password', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $url.'&_target_path='.$this->urlGenerator->generate('app_adhesion_index'),
            ));

            return;
        }
        if ($adherent->isRenaissanceUser() || !$adherent->getSource()) {
            $url .= '&_target_path='.$this->urlGenerator->generate('app_adhesion_index');

            $this->transactionalMailer->sendMessage(AdhesionAlreadySympathizerMessage::create($adherent, $url));

            return;
        }

        $this->logger->error(sprintf('[Adhesion] le compte adhérent [%d] existe déjà (source : %s)', $adherent->getId(), $adherent->getSource()));
    }
}
