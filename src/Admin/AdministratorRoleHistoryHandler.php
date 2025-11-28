<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Administrator;
use App\Entity\Reporting\AdministratorRoleHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AdministratorRoleHistoryHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security,
    ) {
    }

    public function handleChanges(Administrator $administrator, array $oldRoles): void
    {
        /** @var Administrator $author */
        $author = $this->security->getUser();

        $newRoles = $administrator->getAdministratorRoleCodes();

        $addedRoles = array_diff($newRoles, $oldRoles);
        $deletedRoles = array_diff($oldRoles, $newRoles);

        foreach ($addedRoles as $addedRole) {
            $this->entityManager->persist(
                AdministratorRoleHistory::createAdd($administrator, $addedRole, $author)
            );
        }

        foreach ($deletedRoles as $deletedRole) {
            $this->entityManager->persist(
                AdministratorRoleHistory::createRemove($administrator, $deletedRole, $author)
            );
        }

        $this->entityManager->flush();
    }
}
