<?php

namespace App\UserListDefinition;

use App\Entity\EntityUserListDefinitionTrait;
use App\Entity\UserListDefinitionEnum;
use App\Exception\UserListDefinitionException;
use App\Exception\UserListDefinitionMemberException;
use App\Repository\UserListDefinitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserListDefinitionManager
{
    private const STATUS_MEMBER_OF = 'member_of';
    private const STATUS_NOT_MEMBER_OF = 'not_member_of';

    /** @var EntityManagerInterface */
    private $em;

    /** @var UserListDefinitionRepository */
    private $userListDefinitionRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        EntityManagerInterface $em,
        UserListDefinitionRepository $userListDefinitionRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->em = $em;
        $this->userListDefinitionRepository = $userListDefinitionRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getUserListDefinitionMembers(string $type, array $ids, $objectClass): array
    {
        $this->checkType($type);
        $this->checkObjectClass($objectClass);

        $userListDefinitions = $this->userListDefinitionRepository->getForType($type);
        $items = $this->userListDefinitionRepository->getMemberIdsForType($type, $ids, $objectClass);
        foreach ($items as $item) {
            array_walk($userListDefinitions, function (&$userListDefinition) use ($item) {
                if ($userListDefinition['code'] === $item['code']) {
                    $userListDefinition['ids'] = explode(',', $item['ids']);
                }

                return;
            });
        }

        return $userListDefinitions;
    }

    public function updateUserListDefinitionMembers(array $userListDefinitions, string $objectClass): void
    {
        $this->checkObjectClass($objectClass);
        $repository = $this->em->getRepository($objectClass);

        foreach ($userListDefinitions as $memberId => $lists) {
            if (!$member = $repository->find($memberId)) {
                throw new UserListDefinitionMemberException(\sprintf('%s with id "%s" has not been found', $objectClass, $memberId));
            }

            foreach ($lists as $status => $userListDefinitionIds) {
                foreach ($userListDefinitionIds as $userListDefinitionId) {
                    if (!$userListDefinition = $this->userListDefinitionRepository->find($userListDefinitionId)) {
                        throw new UserListDefinitionException(\sprintf('UserListDefinition with id "%s" has not been found', $userListDefinitionId));
                    }

                    if (!$this->authorizationChecker->isGranted(
                        UserListDefinitionPermissions::ABLE_TO_MANAGE_TYPE,
                        $userListDefinition->getType()
                    )) {
                        throw new UserListDefinitionException(\sprintf('UserListDefinition type "%s" cannot be managed by connected user', $userListDefinition->getType()));
                    }

                    if (!$this->authorizationChecker->isGranted(
                        UserListDefinitionPermissions::ABLE_TO_MANAGE_MEMBER,
                        $member
                    )) {
                        throw new UserListDefinitionMemberException(\sprintf('Connected user cannot manage %s member', \get_class($member)));
                    }

                    switch ($status) {
                        case self::STATUS_MEMBER_OF:
                            $member->addUserListDefinition($userListDefinition);

                            break;
                        case self::STATUS_NOT_MEMBER_OF:
                            $member->removeUserListDefinition($userListDefinition);

                            break;
                    }
                }
            }
        }

        $this->em->flush();
    }

    private function checkType(string $type): void
    {
        if (!\in_array($type, UserListDefinitionEnum::TYPES)) {
            throw new UserListDefinitionException("Type '$type' is not supported.");
        }
    }

    private function checkObjectClass(string $objectClass): void
    {
        if (!\in_array(EntityUserListDefinitionTrait::class, class_uses($objectClass))) {
            throw new UserListDefinitionException("Class $objectClass does not use a trait EntityUserListDefinitionTrait");
        }
    }
}
