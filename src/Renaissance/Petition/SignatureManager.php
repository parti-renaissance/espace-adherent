<?php

namespace App\Renaissance\Petition;

use App\Entity\PetitionSignature;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\PetitionConfirmationMessage;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SignatureManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerService $transactionalMailer, private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $secret,
    ) {
    }

    public function save(SignatureRequest $request): void
    {
        $this->entityManager->persist($signature = PetitionSignature::createFromRequest($request));
        $this->entityManager->flush();

        $this->transactionalMailer->sendMessage(PetitionConfirmationMessage::create($signature, $this->generateUrl($signature)));
    }

    private function generateUrl(PetitionSignature $signature): string
    {
        return $this->urlGenerator->generate('app_petition_validate', [
            'uuid' => $signature->getUuid(),
            'token' => JWT::encode(['uuid' => $signature->getUuid()], $this->secret, 'HS256'),
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
