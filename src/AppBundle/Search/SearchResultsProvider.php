<?php

namespace AppBundle\Search;

use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Repository\CommitteeEventRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;

class SearchResultsProvider
{
    private $committee;
    private $membership;
    private $event;

    public function __construct(
        CommitteeRepository $committee,
        CommitteeMembershipRepository $membership,
        CommitteeEventRepository $event
    ) {
        $this->committee = $committee;
        $this->membership = $membership;
        $this->event = $event;
    }

    public function find(SearchParametersFilter $search): array
    {
        if (SearchParametersFilter::TYPE_COMMITTEES === $search->getType()) {
            return $this->findCommittees();
        }

        if (SearchParametersFilter::TYPE_EVENTS === $search->getType()) {
            return $this->findEvents();
        }

        throw new \RuntimeException(sprintf(
            'This provider is not able to handle the search type "%s"',
            $search->getType()
        ));
    }

    public function findCommittees(): array
    {
        // the following part is a fixture only, for this issue

        /** @var Committee[] $committees */
        $committees = $this->committee->findBy([], ['name' => 'asc'], 20);

        foreach ($committees as $committee) {
            $results[] = [
                'committee' => $committee,
                'memberships' => $this->membership->countMembers($committee->getUuid()),
            ];
        }

        return $results ?? [];
    }

    public function findEvents(): array
    {
        // the following part is a fixture only, for this issue

        /** @var CommitteeEvent */
        $events = $this->event->findBy([], ['name' => 'asc'], 20);

        foreach ($events as $event) {
            $results[] = [
                'event' => $event,
                'attendees' => 5,
            ];
        }

        return $results ?? [];
    }
}
