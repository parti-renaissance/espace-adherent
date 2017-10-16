<?php

namespace AppBundle\BoardMember;

use AppBundle\Entity\Adherent;
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

    public function searchMembers(BoardMemberFilter $filter): array
    {
        return $this->getAdherentRepository()->searchBoardMembers($filter);
    }

    public function paginateMembers(BoardMemberFilter $filter): Paginator
    {
        return $this->getAdherentRepository()->paginateBoardMembers($filter);
    }

    public function findRoles(): array
    {
        return $this->getRoleRepository()->findAll();
    }

    private function getAdherentRepository(): AdherentRepository
    {
        return $this->manager->getRepository(Adherent::class);
    }

    private function getRoleRepository(): ObjectRepository
    {
        return $this->manager->getRepository(Role::class);
    }
}
