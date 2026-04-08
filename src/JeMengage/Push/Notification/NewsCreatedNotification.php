<?php

declare(strict_types=1);

namespace App\JeMengage\Push\Notification;

use App\Entity\Jecoute\News;
use App\Firebase\Notification\AbstractMulticastNotification;
use App\JeMengage\Push\NotificationScope;

class NewsCreatedNotification extends AbstractMulticastNotification
{
    public static function create(News $news): self
    {
        if ($news->getCommittee()) {
            $scope = NotificationScope::committee($news->getCommittee()->getId());
        } elseif ($news->isNational()) {
            $scope = NotificationScope::national();
        } else {
            $zone = $news->getZone();
            $assemblyZone = $zone?->getAssemblyZone();

            if (!$assemblyZone) {
                throw new \RuntimeException(\sprintf('News #%d has no assembly zone — cannot resolve notification scope.', $news->getId()));
            }

            $scope = NotificationScope::zone($assemblyZone->getCode());
        }

        return new self(
            $news->getTitle(),
            $news->getCleanedCroppedText(50),
            $scope,
        );
    }
}
