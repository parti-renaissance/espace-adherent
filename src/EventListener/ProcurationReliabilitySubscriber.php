<?php

namespace AppBundle\EventListener;

use AppBundle\Procuration\Event\ProcurationEvents;
use AppBundle\Procuration\Event\ProcurationProxyEvent;
use AppBundle\Procuration\ProcurationReliabilityProcessor;
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
