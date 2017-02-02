<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Geocoder\Coordinates;
use AppBundle\Repository\CommitteeRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CommitteeManager
{
    const EXCLUDE_HOSTS = false;
    const INCLUDE_HOSTS = true;

    private const COMMITTEE_PROPOSALS_COUNT = 3;

    private $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getAdherentCommittees(Adherent $adherent, int $limit = 5): array
    {
        $memberships = $this->getRepository(CommitteeMembership::class)->findMemberships($adherent);

        if (!$memberships->count()) {
            return [];
        }

        // We want the hosted committes first.
        $repository = $this->getRepository(Committee::class);

        $hostedCommittees = $repository->findCommittees(
            $memberships->getCommitteeHostMemberships()->getCommitteeUuids(),
            $limit,
            CommitteeRepository::INCLUDE_UNAPPROVED
        );

        if ($limit === $hostedCommitteesCount = count($hostedCommittees)) {
            return $hostedCommittees;
        }

        // Then, the followed committees.
        $limit -= $hostedCommitteesCount;
        $followedCommittees = $this->getRepository(Committee::class)->findCommittees($memberships->getCommitteeFollowerMemberships()->getCommitteeUuids(), $limit);

        return $hostedCommittees + $followedCommittees;
    }

    public function getTimeline(Committee $committee, int $limit = 30, int $firstResultIndex = 0): Paginator
    {
        $repository = $this->getRepository(CommitteeFeedItem::class);

        return $repository->findPaginatedMostRecentFeedItems((string) $committee->getUuid(), $limit, $firstResultIndex);
    }

    public function getMembersCount(Committee $committee): int
    {
        return $this->getRepository(CommitteeMembership::class)->countMembers($committee->getUuid()->toString());
    }

    public function getCommitteeHosts(Committee $committee): AdherentCollection
    {
        return $this->getRepository(CommitteeMembership::class)->findHostMembers($committee->getUuid()->toString());
    }

    public function getCommitteeFollowers(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->getRepository(CommitteeMembership::class)->findFollowers($committee->getUuid()->toString(), $withHosts);
    }

    public function getOptinCommitteeFollowers(Committee $committee, bool $withHosts = self::INCLUDE_HOSTS): AdherentCollection
    {
        return $this->getCommitteeFollowers($committee, $withHosts)->getCommitteesNotificationsSubscribers();
    }

    public function getNearbyCommittees(Coordinates $coordinates, $limit = self::COMMITTEE_PROPOSALS_COUNT)
    {
        $data = [];
        $committeeMembershipRepository = $this->getRepository(CommitteeMembership::class);
        $committees = $this->getRepository(Committee::class)->findNearbyCommittees($limit, $coordinates);

        foreach ($committees as $committee) {
            $uuid = $committee->getUuid()->toString();

            $data[$uuid] = [
                'committee' => $committee,
                'memberships_count' => $committeeMembershipRepository->countMembers($uuid),
            ];
        }

        return $data;
    }

    /**
     * Makes an adherent follow multiple committees at once.
     *
     * @param Adherent $adherent   The follower
     * @param string[] $committees An array of committee UUIDs
     */
    public function followCommittees(Adherent $adherent, array $committees)
    {
        if (empty($committees)) {
            return;
        }

        foreach ($this->getRepository(Committee::class)->findByUuid($committees) as $committee) {
            $this->followCommittee($adherent, $committee);
        }

        $this->getManager()->flush();
    }

    /**
     * Makes an adherent follow one committee.
     *
     * @param Adherent  $adherent  The follower
     * @param Committee $committee The committee to follow
     * @param bool      $flush     Whether or not to flush the transaction
     */
    public function followCommittee(Adherent $adherent, Committee $committee, $flush = true)
    {
        $manager = $this->getManager();
        $manager->persist($adherent->followCommittee($committee));

        if ($flush) {
            $manager->flush();
        }
    }

    private function getManager(): ObjectManager
    {
        return $this->registry->getManager();
    }

    private function getRepository(string $class): ObjectRepository
    {
        return $this->registry->getRepository($class);
    }
}
