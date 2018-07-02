<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentChangeEmailToken;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\AdherentChangeEmailMessage;
use AppBundle\Repository\AdherentChangeEmailTokenRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdherentChangeEmailHandler
{
    private $mailer;
    private $manager;
    private $repository;
    private $urlGenerator;

    public function __construct(
        MailerService $mailer,
        ObjectManager $manager,
        AdherentChangeEmailTokenRepository $repository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
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
        $adherent->changeEmail($token);
        $this->manager->flush();
    }

    public function sendValidationEmail(Adherent $adherent, AdherentChangeEmailToken $token): void
    {
        $this->mailer->sendMessage(AdherentChangeEmailMessage::createFromAdherent(
            $adherent,
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
