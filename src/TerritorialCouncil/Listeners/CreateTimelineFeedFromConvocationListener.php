<?php

namespace App\TerritorialCouncil\Listeners;

use App\Entity\TerritorialCouncil\Convocation;
use App\Entity\TerritorialCouncil\PoliticalCommitteeFeedItem;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilFeedItem;
use App\TerritorialCouncil\Convocation\Events;
use App\TerritorialCouncil\Event\ConvocationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class CreateTimelineFeedFromConvocationListener implements EventSubscriberInterface
{
    private $entityManager;
    private $engine;

    public function __construct(EntityManagerInterface $entityManager, Environment $engine)
    {
        $this->entityManager = $entityManager;
        $this->engine = $engine;
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
            $item = new TerritorialCouncilFeedItem($instance, $convocation->getCreatedBy(), $this->renderContent($convocation));
        } else {
            $item = new PoliticalCommitteeFeedItem($instance, $convocation->getCreatedBy(), $this->renderContent($convocation));
        }

        $item->setIsLocked(true);

        $this->entityManager->persist($item);
        $this->entityManager->flush();
    }

    private function renderContent(Convocation $convocation): string
    {
        $instance = $convocation->getEntity();

        return $this->engine->render('referent/territorial_council/_convocation_feed_item.html.twig', [
            'description' => $convocation->getDescription(),
            'instance_type' => $instance instanceof TerritorialCouncil ? 'Conseil territorial' : 'Comité politique',
            'is_online_mode' => $convocation->isOnlineMode(),
            'meeting_start_date' => $convocation->getMeetingStartDate(),
            'meeting_url' => $convocation->getMeetingUrl(),
            'president' => $convocation->getCreatedBy(),
            'address' => $convocation->getInlineFormattedAddress(),
        ]);
    }
}
