<?php

namespace App\JeMengage\Hit\Handler;

use App\Entity\Adherent;
use App\Entity\AppHit;
use App\Entity\AppSession;
use App\JeMengage\Hit\Command\SaveAppHitCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AsMessageHandler]
class SaveAppHitCommandHandler
{
    public function __construct(
        private readonly DenormalizerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(SaveAppHitCommand $command): void
    {
        $hit = $this->serializer->denormalize($command->data, AppHit::class, null, ['groups' => ['hit:write']]);
        $hit->raw = $command->data;

        $hit->adherent = $this->entityManager->getReference(Adherent::class, $command->userId);
        $hit->appSession = $command->sessionId ? $this->entityManager->getReference(AppSession::class, $command->sessionId) : null;

        if ($hit->referrerCode) {
            $hit->referrer = $this->adherentRepository->findByPublicId($hit->referrerCode, true);
        }

        $this->entityManager->persist($hit);
        $this->entityManager->flush();
    }
}
