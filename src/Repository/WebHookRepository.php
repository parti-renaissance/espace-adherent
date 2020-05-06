<?php

namespace App\Repository;

use App\Entity\WebHook\WebHook;
use App\WebHook\Event;
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
            ->select('web_hook.callbacks, web_hook.service')
            ->where('web_hook.event = :event')
            ->setParameter('event', $event->getValue())
            ->getQuery()
            ->getArrayResult()
        ;

        if (!$callbacks) {
            return [];
        }

        $arrCallbacks = [];
        foreach ($callbacks as $callback) {
            foreach ($callback['callbacks'] as $cb) {
                if (isset($arrCallbacks[$cb]) && $callback['service']) {
                    $arrCallbacks[$cb] = [
                        'services' => array_merge($arrCallbacks[$cb]['service'], [$callback['service']]),
                    ];
                } elseif ($callback['service']) {
                    $arrCallbacks[$cb] = ['services' => $callback['service']];
                } elseif (!isset($arrCallbacks[$cb])) {
                    $arrCallbacks[$cb] = [];
                }
            }
        }

        return $arrCallbacks;
    }

    public function save(WebHook $webHook): void
    {
        $this->_em->persist($webHook);
        $this->_em->flush($webHook);
    }
}
