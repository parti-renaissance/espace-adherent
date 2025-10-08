<?php

namespace App\JeMengage\Hit\Stats\Provider;

use App\Entity\AdherentMessage\AdherentMessage;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use Ramsey\Uuid\UuidInterface;

class PublicationProvider extends AbstractProvider
{
    public function __construct(
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function provide(TargetTypeEnum $type, UuidInterface $objectUuid): array
    {
        /** @var AdherentMessage $message */
        $message = $this->adherentMessageRepository->findOneByUuid($objectUuid);

        return [
            'sent_at' => $message->getSentAt(),
            'visible_count' => $this->adherentRepository->countAdherentsForMessage($message),
            'contacts' => $this->adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: true, asUnion: true),
            'unique_notifications' => $this->adherentRepository->countAdherentsForMessage($message, byPush: true),
            'unique_emails' => $this->adherentRepository->countAdherentsForMessage($message, byEmail: true),
        ];
    }

    public function support(TargetTypeEnum $targetType): bool
    {
        return TargetTypeEnum::Publication === $targetType;
    }
}
