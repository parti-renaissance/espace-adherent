<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentChangeEmailToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceAdherentChangeEmailMessage;
use App\Membership\Event\UserEmailEvent;
use App\Membership\Event\UserEvent;
use App\Repository\AdherentChangeEmailTokenRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentChangeEmailHandler
{
    private MailerService $mailer;
    private ObjectManager $manager;
    private AdherentChangeEmailTokenRepository $repository;
    private UrlGeneratorInterface $urlGenerator;
    private EventDispatcherInterface $dispatcher;
    private string $renaissanceHost;

    public function __construct(
        MailerService $transactionalMailer,
        ObjectManager $manager,
        AdherentChangeEmailTokenRepository $repository,
        UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $dispatcher,
        string $renaissanceHost
    ) {
        $this->mailer = $transactionalMailer;
        $this->manager = $manager;
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
        $this->dispatcher = $dispatcher;
        $this->renaissanceHost = $renaissanceHost;
    }

    public function handleRequest(Adherent $adherent, string $newEmailAddress): void
    {
        $token = AdherentChangeEmailToken::generate($adherent);
        $token->setEmail(mb_strtolower($newEmailAddress));

        $this->manager->persist($token);
        $this->manager->flush();

        $this->sendValidationEmail($adherent, $token);

        $this->repository->invalidateOtherActiveToken($adherent, $token);
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

        $this->mailer->sendMessage(RenaissanceAdherentChangeEmailMessage::createFromAdherent($adherent, $token->getEmail(), $confirmationLink));
    }

    private function createConfirmationLink(Adherent $adherent, AdherentChangeEmailToken $token): string
    {
        $params = [
            'adherent_uuid' => $adherent->getUuidAsString(),
            'change_email_token' => $token->getValue(),
            'app_domain' => $this->renaissanceHost,
        ];

        return $this->urlGenerator->generate('user_validate_new_email', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
