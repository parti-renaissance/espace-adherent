<?php

declare(strict_types=1);

namespace App\Renaissance\Petition;

use App\Entity\PetitionSignature;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\PetitionConfirmationMessage;
use App\Mailer\Message\Renaissance\PetitionConfirmationReminderMessage;
use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Notifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $secret,
    ) {
    }

    public function sendConfirmation(PetitionSignature $signature): void
    {
        $this->transactionalMailer->sendMessage(PetitionConfirmationMessage::create($signature, $this->generateConfirmUrl($signature)));
    }

    public function sendReminder(PetitionSignature $signature): void
    {
        $this->transactionalMailer->sendMessage(PetitionConfirmationReminderMessage::create($signature, $this->generateConfirmUrl($signature)));
    }

    private function generateConfirmUrl(PetitionSignature $signature): string
    {
        return $this->urlGenerator->generate('app_petition_validate', [
            'uuid' => $signature->getUuid(),
            'token' => JWT::encode(['uuid' => $signature->getUuid()], $this->secret, 'HS256'),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
