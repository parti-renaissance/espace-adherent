<?php

declare(strict_types=1);

namespace App\JeMengage\Hit\Stats\Provider;

use App\Entity\AdherentMessage\AdherentMessage;
use App\JeMengage\Hit\Stats\DTO\StatsOutput;
use App\JeMengage\Hit\TargetTypeEnum;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use Ramsey\Uuid\UuidInterface;

class PublicationProvider extends AbstractProvider
{
    private int $tryCount = 0;

    public function __construct(
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function provide(TargetTypeEnum $type, UuidInterface $objectUuid, StatsOutput $output, bool $wait = false): array
    {
        /** @var AdherentMessage $message */
        $message = $this->adherentMessageRepository->findOneByUuid($objectUuid);

        $allReach = $this->adherentMessageRepository->countReachAll($message->getId());

        $totalReachByEmail = null;
        if ($message->isFullySent() && ($allReach['email'] > 0 || !$message->getRecipientCount())) {
            $totalReachByEmail = $allReach['email'];
        }

        $totalReachByPush = $allReach['push'];

        if ($wait && !$totalReachByPush && ++$this->tryCount < 10) {
            sleep(1);

            return $this->provide($type, $objectUuid, $output, $wait);
        }

        $uniqueImpressions = $output->get('unique_impressions');

        $result = [
            'sent_at' => $message->getSentAt()?->format(\DateTimeInterface::RFC3339),
            'visible_count' => $wait ? $this->adherentRepository->countAdherentsForMessage($message) : null,
            'contacts' => $allReach['email_push'],
            'unique_notifications' => $totalReachByPush,
            'unique_emails' => $totalReachByEmail,
            'unique_opens__notification_rate' => $totalReachByPush > 0 ? ($output->get('unique_opens__notification') * 100.0 / $totalReachByPush) : 0.0,
            'unique_opens__app_rate' => $uniqueImpressions > 0 ? ($output->get('unique_opens__app') * 100.0 / $uniqueImpressions) : 0.0,
            'unique_opens__email_rate' => $totalReachByEmail > 0 ? ($output->get('unique_opens__email') * 100.0 / $totalReachByEmail) : 0.0,
            'unsubscribed' => $unsubscribed = $message->getUnsubscribedCount(),
            'unsubscribed__total_rate' => $totalReachByEmail > 0 ? ($unsubscribed * 100.0 / $totalReachByEmail) : 0.0,
            'notifications' => [
                'web' => $allReach['push_web'],
                'ios' => $allReach['push_ios'],
                'android' => $allReach['push_android'],
            ],
        ];

        if ($allReach['total'] > 0) {
            $result['unique_opens__total_rate'] = $output->get('unique_opens') * 100.0 / $allReach['total'];
        }

        return $result;
    }

    public function support(TargetTypeEnum $targetType): bool
    {
        return TargetTypeEnum::Publication === $targetType;
    }
}
