<?php

namespace App\Firebase;

use App\Entity\Notification as NotificationEntity;
use App\Firebase\Notification\MulticastNotificationInterface;
use App\Firebase\Notification\NotificationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Contract\Messaging as BaseMessaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class JeMarcheMessaging
{
    private const MULTICAST_MAX_TOKENS = 500;

    public function __construct(
        private readonly BaseMessaging $messaging,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function send(NotificationInterface $notification): void
    {
        $notificationEntity = NotificationEntity::create($notification);

        $this->entityManager->persist($notificationEntity);
        $this->entityManager->flush();

        if ($notification instanceof MulticastNotificationInterface) {
            $this->sendToDevices($notification);
        } else {
            throw new \InvalidArgumentException(\sprintf('%s" is neither a topic nor a multicast notification.', $notification::class));
        }

        $notificationEntity->setDelivered();

        $this->entityManager->flush();
    }

    private function sendToDevices(MulticastNotificationInterface $notification): void
    {
        $message = $this
            ->createMessage()
            ->withNotification($this->createNotification($notification))
            ->withWebPushConfig($this->getPushConfig($notification))
            ->withData($this->getData($notification))
        ;

        foreach (array_chunk($notification->getTokens(), self::MULTICAST_MAX_TOKENS) as $chunk) {
            $this->messaging->sendMulticast($message, $chunk);
        }
    }

    private function createMessage(): CloudMessage
    {
        return CloudMessage::new();
    }

    private function createNotification(NotificationInterface $notification): Notification
    {
        return Notification::create($notification->getTitle(), $notification->getBody());
    }

    private function getPushConfig(NotificationInterface $notification): array
    {
        return [
            'notification' => [
                'icon', 'https://app.parti-renaissance.fr/images/icons/icon-512x512.png',
            ],
            'fcm_options' => [
                'link', $notification->getData()['deeplink'] ?? null,
            ],
        ];
    }

    private function getData(MulticastNotificationInterface $notification): array
    {
        $data = $notification->getData();

        if (!empty($data['deeplink'])) {
            $data['deeplink'] = parse_url($data['deeplink'], \PHP_URL_PATH);
        }

        return $data;
    }
}
