<?php

namespace AppBundle\BoardMember;

use AppBundle\Collection\AdherentCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BoardMember\BoardMember;
use AppBundle\Entity\BoardMember\Role;
use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BoardMemberManager
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function searchMembers(BoardMemberFilter $filter, Adherent $excludedMember): array
    {
        return $this->getAdherentRepository()->searchBoardMembers($filter, $excludedMember);
    }

    public function paginateMembers(BoardMemberFilter $filter, Adherent $excludedMember): Paginator
    {
        return $this->getAdherentRepository()->paginateBoardMembers($filter, $excludedMember);
    }

    public function findSavedMembers(Adherent $member): AdherentCollection
    {
        return $this->getAdherentRepository()->findSavedBoardMember($member->getBoardMember());
    }

    public function findRoles(): array
    {
        return $this->getRoleRepository()->findAll();
    }

    public function getStatistics(AdherentCollection $savedBoardMembers): array
    {
        $statistics['women'] = 0;
        $statistics['men'] = 0;
        $statistics['average_age'] = 0;
        $statistics['areas'] = [];
        foreach (BoardMember::getAreas() as $area) {
            $statistics['areas']['board_member.stats.area.'.$area] = 0;
        }

        if ($savedBoardMembers->count() > 0) {
            $sum_age = 0;
            $count = $savedBoardMembers->count();

            foreach ($savedBoardMembers as $member) {
                if ($member->isFemale()) {
                    $statistics['women'] += 1;
                } else {
                    $statistics['men'] += 1;
                }

                $sum_age += $member->getAge();
                $statistics['areas']['board_member.stats.area.'.$member->getBoardMemberArea()] += 1;
            }

            $statistics['average_age'] = round($sum_age / $count);
            $statistics['women'] = round($statistics['women'] / $count, 2) * 100;
            $statistics['men'] = round($statistics['men'] / $count, 2) * 100;
            foreach ($statistics['areas'] as $area => $value) {
                $statistics['areas'][$area] = round($value / $count, 2) * 100;
            }
        }

        return $statistics;
    }

    private function getAdherentRepository(): AdherentRepository
    {
        return $this->manager->getRepository(Adherent::class);
    }

    public function getBoardMemberRepository(): ObjectRepository
    {
        return $this->manager->getRepository(BoardMember::class);
    }

    private function getRoleRepository(): ObjectRepository
    {
        return $this->manager->getRepository(Role::class);
    }
}
