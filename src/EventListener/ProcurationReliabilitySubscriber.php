<?php

namespace App\EventListener;

use App\Procuration\Event\ProcurationEvents;
use App\Procuration\Event\ProcurationProxyEvent;
use App\Procuration\ProcurationReliabilityProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProcurationReliabilitySubscriber implements EventSubscriberInterface
{
    private $procurationReliabilityProcessor;
    private $em;

    public function __construct(
        ProcurationReliabilityProcessor $procurationReliabilityProcessor,
        EntityManagerInterface $em
    ) {
        $this->procurationReliabilityProcessor = $procurationReliabilityProcessor;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            ProcurationEvents::PROXY_REGISTRATION => 'processReliability',
        ];
    }

    public function processReliability(ProcurationProxyEvent $event): void
    {
        $this->procurationReliabilityProcessor->process($event->getProxy());

        $this->em->flush();
    }
}
