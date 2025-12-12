<?php

declare(strict_types=1);

namespace App\Renaissance\Petition;

use App\Adherent\Command\UpdateAdherentLinkCommand;
use App\Entity\PetitionSignature;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Messenger\MessageBusInterface;

class SignatureManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
        private readonly Notifier $notifier,
        private readonly string $secret,
    ) {
    }

    public function save(SignatureRequest $request): void
    {
        $this->entityManager->persist($signature = PetitionSignature::createFromRequest($request));
        $this->entityManager->flush();

        $this->notifier->sendConfirmation($signature);
    }

    public function validate(PetitionSignature $signature, string $token): void
    {
        if ($signature->validatedAt) {
            return;
        }

        if (
            !($uuidFromToken = JWT::decode($token, new Key($this->secret, 'HS256'))?->uuid)
            || $uuidFromToken !== $signature->getUuid()->toString()
        ) {
            throw new \InvalidArgumentException('Le token de confirmation est invalide');
        }

        $signature->validate();
        $this->entityManager->flush();

        $this->messageBus->dispatch(new UpdateAdherentLinkCommand($signature->getUuid(), $signature::class));
    }

    public function remind(PetitionSignature $signature): void
    {
        if ($signature->validatedAt || $signature->remindedAt) {
            return;
        }

        $this->notifier->sendReminder($signature);

        $signature->remindedAt = new \DateTime();
        $this->entityManager->flush();
    }
}
