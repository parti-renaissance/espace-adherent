<?php

namespace App\BoardMember;

use App\Collection\AdherentCollection;
use App\Entity\Adherent;
use App\Entity\BoardMember\BoardMember;
use App\Repository\AdherentRepository;
use App\Repository\BoardMember\RoleRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BoardMemberManager
{
    private $adherentRepository;
    private $roleRepository;

    public function __construct(AdherentRepository $adherentRepository, RoleRepository $roleRepository)
    {
        $this->adherentRepository = $adherentRepository;
        $this->roleRepository = $roleRepository;
    }

    public function searchMembers(BoardMemberFilter $filter, Adherent $excludedMember): array
    {
        return $this->adherentRepository->searchBoardMembers($filter, $excludedMember);
    }

    public function paginateMembers(BoardMemberFilter $filter, Adherent $excludedMember): Paginator
    {
        return $this->adherentRepository->paginateBoardMembers($filter, $excludedMember);
    }

    public function findSavedMembers(Adherent $member): AdherentCollection
    {
        return $this->adherentRepository->findSavedBoardMember($member->getBoardMember());
    }

    public function findRoles(): array
    {
        return $this->roleRepository->findAll();
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
                $member->isFemale() ? ++$statistics['women'] : ++$statistics['men'];
                $sum_age += $member->getAge();
                ++$statistics['areas']['board_member.stats.area.'.$member->getBoardMember()->getArea()];
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
}
