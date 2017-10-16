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
        $owner = $this->getBoardMemberRepository()->findOneByAdherent(['id' => $member]);

        return $this->getAdherentRepository()->findSavedBoardMember($owner);
    }

    public function findRoles(): array
    {
        return $this->getRoleRepository()->findAll();
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
