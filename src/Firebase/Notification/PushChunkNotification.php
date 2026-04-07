<?php

declare(strict_types=1);

namespace App\Firebase\Notification;

class PushChunkNotification extends AbstractMulticastNotification
{
    public function __construct(
        string $title,
        string $body,
        array $data,
        ?string $scope,
        private string $originalClassName,
    ) {
        parent::__construct($title, $body, $data);
        $this->setScope($scope);
    }

    public function getOriginalClassName(): string
    {
        return $this->originalClassName;
    }
}
