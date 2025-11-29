<?php

declare(strict_types=1);

namespace App\AdherentMessage\Listener;

use App\Adherent\Tag\TagEnum;
use App\AdherentMessage\Events;
use App\AdherentMessage\MessageEvent;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Repository\CommitteeRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CreateDefaultMessageFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly CommitteeRepository $committeeRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::MESSAGE_PRE_CREATE => ['createDefaultMessageFilter', 1000],
        ];
    }

    public function createDefaultMessageFilter(MessageEvent $event): void
    {
        $message = $event->getMessage();

        if ($message->getFilter() || !$message->getAuthor()) {
            return;
        }

        if (!$scope = $this->scopeGeneratorResolver->generate()) {
            return;
        }

        $filter = new AudienceFilter();

        if (!$message->isStatutory()) {
            $filter->adherentTags = TagEnum::ADHERENT;
        }

        $filter->setScope($scope->getMainCode());

        if (!$scope->isNational()) {
            $filter->setZones($zones = $scope->getZones());
            if ($zones) {
                $filter->setZone($zones[0]);
            }

            if ($committeeUuids = $scope->getCommitteeUuids()) {
                $filter->setCommittee($this->committeeRepository->findOneByUuid(current($committeeUuids)));
            }
        }

        $message->setFilter($filter);
    }
}
