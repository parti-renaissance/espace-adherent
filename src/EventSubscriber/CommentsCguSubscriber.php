<?php

namespace AppBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

final class CommentsCguSubscriber implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['postDeserialize', EventPriorities::POST_DESERIALIZE],
        ];
    }

    public function postDeserialize(GetResponseEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');

        if (!\in_array($route, ['api_threads_post_collection', 'api_thread_comments_post_collection'])) {
            return;
        }

        $user = $this->security->getUser();

        if ($user) {
            $content = json_decode($event->getRequest()->getContent(), true);

            $user->setCommentsCguAccepted(filter_var(
                $content['comments_cgu_accepted'] ?? null,
                \FILTER_VALIDATE_BOOLEAN
            ));
        }
    }
}
