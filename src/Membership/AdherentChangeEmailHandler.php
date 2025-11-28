<?php

declare(strict_types=1);

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceAdherentChangeEmailMessage;
use App\Membership\Event\UserEmailEvent;
use App\Membership\Event\UserEvent;
use App\Repository\AdherentChangeEmailTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentChangeEmailHandler
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly EntityManagerInterface $manager,
        private readonly AdherentChangeEmailTokenRepository $repository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handleRequest(Adherent $adherent, string $newEmailAddress): void
    {
        $token = AdherentChangeEmailToken::generate($adherent);
        $token->setEmail(mb_strtolower($newEmailAddress));

        $this->manager->persist($token);
        $this->manager->flush();

        $this->sendValidationEmail($adherent, $token);

        $this->repository->invalidateOtherActiveToken($adherent, $token);

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_EMAIL_CHANGE_REQUEST);
    }

    public function handleValidationRequest(Adherent $adherent, AdherentChangeEmailToken $token): void
    {
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

        $oldEmail = $adherent->getEmailAddress();

        $adherent->changeEmail($token);
        $this->manager->flush();

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATED);
        $this->dispatcher->dispatch(new UserEmailEvent($adherent, $oldEmail), UserEvents::USER_EMAIL_UPDATED);
    }

    public function sendValidationEmail(Adherent $adherent, AdherentChangeEmailToken $token): void
    {
        $confirmationLink = $this->createConfirmationLink($adherent, $token);

        $this->transactionalMailer->sendMessage(RenaissanceAdherentChangeEmailMessage::createFromAdherent($adherent, $token->getEmail(), $confirmationLink));
    }

    private function createConfirmationLink(Adherent $adherent, AdherentChangeEmailToken $token): string
    {
        $params = [
            'adherent_uuid' => $adherent->getUuidAsString(),
            'change_email_token' => $token->getValue(),
        ];

        return $this->urlGenerator->generate('user_validate_new_email', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
