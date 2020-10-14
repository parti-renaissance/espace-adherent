<?php

namespace App\TerritorialCouncil\Listeners;

use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use App\TerritorialCouncil\Convocation\Events;
use App\TerritorialCouncil\Event\ConvocationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateTimelineFeedFromConvocationListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::CONVOCATION_CREATED => ['onConvocationCreate', -100],
        ];
    }

    public function onConvocationCreate(ConvocationEvent $event): void
    {
        $convocation = $event->getConvocation();
        $instance = $convocation->getEntity();

        if ($instance instanceof TerritorialCouncil) {
            $item = new TerritorialCouncilFeedItem($instance, $convocation->getCreatedBy(), $convocation->getDescription());
        } else {
            $item = new PoliticalCommitteeFeedItem($instance, $convocation->getCreatedBy(), $convocation->getDescription());
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }
}
