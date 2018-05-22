<?php

namespace AppBundle\Repository;

use AppBundle\Entity\WebHook\WebHook;
use AppBundle\WebHook\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class WebHookRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, WebHook::class);
    }

    public function findCallbacksByEvent(Event $event): array
    {
        $callbacks = $this->createQueryBuilder('web_hook')
            ->select('web_hook.callbacks')
            ->where('web_hook.event = :event')
            ->setParameter('event', $event->getValue())
            ->getQuery()
            ->getArrayResult()
        ;

        if (!$callbacks) {
            return [];
        }

        $callbacks = array_column($callbacks, 'callbacks');
        $callbacks = array_merge(...$callbacks);

        return array_unique($callbacks);
    }

    public function save(WebHook $webHook): void
    {
        $this->_em->persist($webHook);
        $this->_em->flush($webHook);
    }
}
