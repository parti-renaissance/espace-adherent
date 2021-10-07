<?php

namespace App\Adherent\Handler;

use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Entity\AdherentEmailSubscribeToken;
use App\Mailer\MailerService;
use App\Mailer\Message\AdherentResubscribeEmailMessage;
use App\Repository\AdherentEmailSubscribeTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendResubscribeEmailCommandHandler implements MessageHandlerInterface
{
    private MailerService $mailer;
    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;
    private AdherentEmailSubscribeTokenRepository $emailSubscribeTokenRepository;

    public function __construct(
        MailerService $transactionalMailer,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManager,
        AdherentEmailSubscribeTokenRepository $emailSubscribeTokenRepository
    ) {
        $this->mailer = $transactionalMailer;
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
        $this->emailSubscribeTokenRepository = $emailSubscribeTokenRepository;
    }

    public function __invoke(SendResubscribeEmailCommand $command): void
    {
        $token = $this->generateNewToken($command);

        $adherent = $command->getAdherent();

        $this->mailer->sendMessage(AdherentResubscribeEmailMessage::create(
            $adherent,
            $this->urlGenerator->generate('app_adherent_profile_email_subscribe', [
                'adherent_uuid' => $adherent->getUuid(),
                'email_subscribe_token' => $token->getValue(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    private function generateNewToken(SendResubscribeEmailCommand $command): AdherentEmailSubscribeToken
    {
        $adherent = $command->getAdherent();

        foreach ($this->emailSubscribeTokenRepository->findAllAvailable($adherent) as $token) {
            $token->invalidate();
        }

        $this->entityManager->persist($token = AdherentEmailSubscribeToken::generate($adherent, AdherentEmailSubscribeToken::DURATION));
        $token->setTriggerSource($command->getTriggerSource());

        $this->entityManager->flush();

        return $token;
    }
}
