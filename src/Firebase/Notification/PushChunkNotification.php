<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

use Symfony\Component\Uid\Uuid;

class PushChunkNotification extends AbstractMulticastNotification
{
    public function __construct(
        string $title,
        string $body,
        string $scope,
        array $data,
        public readonly string $originalClassName,
        public readonly ?Uuid $pushNotificationUuid = null,
    ) {
        parent::__construct($title, $body, $scope, $data);
    }
}
