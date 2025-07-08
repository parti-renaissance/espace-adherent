<?php

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

        if (!$message->isStatutory()) {
            return;
        }

        if (!$scopeGenerator = $this->scopeGeneratorResolver->resolve()) {
            return;
        }

        $scope = $scopeGenerator->generate($message->getAuthor());

        $filter = new AudienceFilter();

        if (!$message->isStatutory()) {
            $filter->adherentTags = TagEnum::ADHERENT;
        }

        $filter->setScope($scope->getMainCode());
        $filter->setZones($scope->getZones());

        if ($committeeUuids = $scope->getCommitteeUuids()) {
            $filter->setCommittee($this->committeeRepository->findOneByUuid(current($committeeUuids)));
        }

        $message->setFilter($filter);
    }
}
