<?php

namespace App\ElectedRepresentative;

use App\Entity\ElectedRepresentative\ElectedRepresentative;
use App\Entity\ElectedRepresentative\Reporting\UserListDefinitionHistory;
use App\Utils\ArrayUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserListDefinitionHistoryManager
{
    private $tokenStorage;
    private $entityManager;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    public function handleChanges(ElectedRepresentative $electedRepresentative, array $oldUserListDefinitions): void
    {
        $user = $this->getCurrentUser();

        $newUserListDefinitions = $electedRepresentative->getUserListDefinitions()->toArray();

        $userListDefinitionsToRemove = ArrayUtils::arrayDiffRecursive(
            $oldUserListDefinitions,
            $newUserListDefinitions
        );

        foreach ($userListDefinitionsToRemove as $userListDefinitionToRemove) {
            $history = UserListDefinitionHistory::createRemove(
                $user,
                $electedRepresentative,
                $userListDefinitionToRemove
            );

            $this->entityManager->persist($history);
        }

        $userListDefinitionsToAdd = ArrayUtils::arrayDiffRecursive(
            $newUserListDefinitions,
            $oldUserListDefinitions
        );

        foreach ($userListDefinitionsToAdd as $userListDefinitionToAdd) {
            $history = UserListDefinitionHistory::createAdd(
                $user,
                $electedRepresentative,
                $userListDefinitionToAdd
            );

            $this->entityManager->persist($history);
        }
    }

    private function getCurrentUser(): UserInterface
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
