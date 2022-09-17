<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Mailer\MailerService;
use App\Mailer\Message;
use App\OAuth\CallbackManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MembershipNotifier
{
    private CallbackManager $callbackManager;
    private MailerService $mailer;
    private EntityManagerInterface $manager;

    public function __construct(
        CallbackManager $callbackManager,
        MailerService $transactionalMailer,
        EntityManagerInterface $manager
    ) {
        $this->callbackManager = $callbackManager;
        $this->mailer = $transactionalMailer;
        $this->manager = $manager;
    }

    public function sendEmailValidation(Adherent $adherent, bool $isReminder = false): bool
    {
        $token = AdherentActivationToken::generate($adherent);

        $this->manager->persist($token);
        $this->manager->flush();

        $activationUrl = $this->generateMembershipActivationUrl($adherent, $token);

        if ($isReminder) {
            return MembershipSourceEnum::RENAISSANCE === $adherent->getSource()
                ? $this->mailer->sendMessage(Message\Renaissance\RenaissanceAdherentAccountActivationReminderMessage::create($adherent, $activationUrl))
                : $this->mailer->sendMessage(Message\AdherentAccountActivationReminderMessage::create($adherent, $activationUrl))
            ;
        }

        return MembershipSourceEnum::RENAISSANCE === $adherent->getSource()
            ? $this->mailer->sendMessage(Message\Renaissance\RenaissanceAdherentAccountActivationMessage::create($adherent, $activationUrl))
            : $this->mailer->sendMessage(Message\AdherentAccountActivationMessage::create($adherent, $activationUrl))
        ;
    }

    public function sendEmailReminder(Adherent $adherent): bool
    {
        $donationUrl = $this->callbackManager->generateUrl('donation_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->mailer->sendMessage(Message\AdherentMembershipReminderMessage::create($adherent, $donationUrl));
    }

    public function sendConfirmationJoinMessage(Adherent $adherent): bool
    {
        return MembershipSourceEnum::RENAISSANCE === $adherent->getSource()
            ? $this->mailer->sendMessage(Message\Renaissance\RenaissanceAdherentAccountConfirmationMessage::createFromAdherent($adherent))
            : $this->mailer->sendMessage(Message\AdherentAccountConfirmationMessage::createFromAdherent($adherent))
        ;
    }

    public function sendUnregistrationMessage(Adherent $adherent): void
    {
        $this->mailer->sendMessage(Message\AdherentTerminateMembershipMessage::createFromAdherent($adherent));
    }

    private function generateMembershipActivationUrl(Adherent $adherent, AdherentActivationToken $token): string
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'activation_token' => (string) $token->getValue(),
        ];

        return $this->callbackManager->generateUrl(
            MembershipSourceEnum::RENAISSANCE === $adherent->getSource() ? 'app_renaissance_membership_activate' : 'app_membership_activate',
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
