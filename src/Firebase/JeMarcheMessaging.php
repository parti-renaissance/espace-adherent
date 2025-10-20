<?php

namespace App\Firebase;

use App\Entity\Notification as NotificationEntity;
use App\Firebase\Event\PushNotificationSentEvent;
use App\Firebase\Notification\MulticastNotificationInterface;
use App\Firebase\Notification\NotificationInterface;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Contract\Messaging as BaseMessaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JeMarcheMessaging
{
    public const MULTICAST_MAX_TOKENS = 500;

    public function __construct(
        private readonly BaseMessaging $messaging,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function send(NotificationInterface $notification): void
    {
        $notificationEntityTemplate = NotificationEntity::create($notification);

        if ($notification instanceof MulticastNotificationInterface) {
            $message = $this
                ->createMessage()
                ->withNotification($this->createNotification($notification))
                ->withWebPushConfig($this->getPushConfig($notification))
                ->withData($this->getData($notification))
            ;

            foreach (array_chunk($notification->getTokens(), self::MULTICAST_MAX_TOKENS) as $chunk) {
                $this->entityManager->persist($notificationEntity = $notificationEntityTemplate->withTokens($chunk));
                $this->entityManager->flush();

                $this->messaging->sendMulticast($message, $chunk);

                $notificationEntity->setDelivered();
                $this->entityManager->flush();

                $this->eventDispatcher->dispatch(new PushNotificationSentEvent($notificationEntity));
            }
        } else {
            throw new \InvalidArgumentException(\sprintf('%s" is neither a topic nor a multicast notification.', $notification::class));
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
                'icon' => 'https://app.parti-renaissance.fr/images/icons/icon-512x512.png',
            ],
            'fcm_options' => [
                'link' => $notification->getData()['link'] ?? null,
            ],
        ];
    }

    private function getData(MulticastNotificationInterface $notification): array
    {
        $data = $notification->getData();

        if (!empty($data['link'])) {
            $link = $data['link'];
            $data['link'] = parse_url($link, \PHP_URL_PATH);
            $queryParams = parse_url($link, \PHP_URL_QUERY);
            $data['link'] .= $queryParams ? '?'.$queryParams : '';
        }

        return $data;
    }
}
