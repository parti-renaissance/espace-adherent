<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentChangeEmailToken;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentChangeEmailMessage;
use AppBundle\Repository\AdherentChangeEmailTokenRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdherentChangeEmailHandler
{
    private $mailer;
    private $manager;
    private $repository;
    private $urlGenerator;
    private $dispatcher;

    public function __construct(
        MailerService $mailer,
        ObjectManager $manager,
        AdherentChangeEmailTokenRepository $repository,
        UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $dispatcher
    ) {
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
        $this->dispatcher = $dispatcher;
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
        $this->dispatcher->dispatch(UserEvents::USER_BEFORE_UPDATE, new UserEvent($adherent));

        $adherent->changeEmail($token);
        $this->manager->flush();

        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($adherent));
    }

    public function sendValidationEmail(Adherent $adherent, AdherentChangeEmailToken $token): void
    {
        $this->mailer->sendMessage(AdherentChangeEmailMessage::createFromAdherent(
            $adherent,
            $token->getEmail(),
            $this->urlGenerator->generate(
                'user_validate_new_email',
                [
                    'adherent_uuid' => $adherent->getUuidAsString(),
                    'change_email_token' => $token->getValue(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ));
    }
}
