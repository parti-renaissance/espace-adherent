<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Mailer\MailerService;
use App\Mailer\Message;
use App\Mailer\Message\Renaissance\RenaissanceAdherentAccountActivationMessage;
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

        if (MembershipSourceEnum::RENAISSANCE === $adherent->getSource() && $adherent->getActivatedAt()) {
            return $this->mailer->sendMessage(Message\Renaissance\RenaissanceAdherentAccountConfirmationMessage::createFromAdherent($adherent));
        }

        return $this->mailer->sendMessage(Message\AdherentAccountActivationMessage::create($adherent, $activationUrl));
    }

    public function sendRenaissanceValidationEmail(AdherentRequest $adherentRequest): void
    {
        $activationUrl = $this->callbackManager->generateUrl(
            'app_renaissance_membership_activate',
            [
                'uuid' => $adherentRequest->getUuid()->toString(),
                'token' => $adherentRequest->token->toString(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailer->sendMessage(RenaissanceAdherentAccountActivationMessage::create($adherentRequest, $activationUrl));
    }

    public function sendEmailReminder(Adherent $adherent): bool
    {
        $donationUrl = $this->callbackManager->generateUrl('donation_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->mailer->sendMessage(Message\AdherentMembershipReminderMessage::create($adherent, $donationUrl));
    }

    public function sendConfirmationJoinMessage(Adherent $adherent): void
    {
        MembershipSourceEnum::RENAISSANCE === $adherent->getSource()
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
