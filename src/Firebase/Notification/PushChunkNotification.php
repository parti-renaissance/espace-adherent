<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

use Ramsey\Uuid\UuidInterface;

class PushChunkNotification extends AbstractMulticastNotification
{
    public function __construct(
        string $title,
        string $body,
        array $data,
        ?string $scope,
        public readonly string $originalClassName,
        public readonly ?UuidInterface $pushNotificationUuid = null,
    ) {
        parent::__construct($title, $body, $data);
        $this->setScope($scope);
    }
}
