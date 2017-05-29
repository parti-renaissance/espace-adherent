<?php

namespace AppBundle\Notification;

use AppBundle\Entity\Article;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use sngrl\PhpFirebaseCloudMessaging\ClientInterface;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SendOnArticlePublished implements EventSubscriber
{
    const TOPIC = 'news';

    private $firebaseClient;
    private $router;
    private $logger;

    public function __construct(ClientInterface $firebaseClient, RouterInterface $router, LoggerInterface $logger)
    {
        $this->firebaseClient = $firebaseClient;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::preUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Article) {
            return;
        }

        if ($entity->isPublished()) {
            $this->notify($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Article) {
            return;
        }

        if ($args->hasChangedField('published') && $entity->isPublished()) {
            $this->notify($entity);
        }
    }

    private function notify(Article $article): void
    {
        $notification = new Notification($article->getTitle(), $article->getDescription());
        $notification->setClickAction($this->router->generate('article_view', ['slug' => $article->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL));
        $notification->setIcon('/favicon-large.jpg');

        $message = new Message();
        $message->setPriority('high');
        $message->addRecipient(new Topic(self::TOPIC));
        $message->setNotification($notification);

        try {
            $response = $this->firebaseClient->send($message);
            if ($response->getStatusCode() >= 400) {
                $this->logger->warning('Call to Firebase Cloud Messaging failed.', ['response' => $response]);
            }
        } catch (RequestException $e) {
            $this->logger->warning(
                sprintf(
                    'Firebase Cloud Messaging http request failed: %s',
                    $e->getMessage()
                ),
                ['exception' => $e]
            );
        }
    }
}
