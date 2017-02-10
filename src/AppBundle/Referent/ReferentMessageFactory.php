<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;

class ReferentMessageFactory
{
    /**
     * @var ManagedUserFactory
     */
    private $usersListBuilder;

    public function __construct(ManagedUserFactory $usersListBuilder)
    {
        $this->usersListBuilder = $usersListBuilder;
    }

    public function createReferentMessageFor(Adherent $referent, array $selected): ReferentMessage
    {
        $allowedManagedUsers = $this->createManagedUsersListIndexedByTypeAndId($referent);
        $selectedManagedUser = [];

        foreach ($selected as $user) {
            if (!isset($allowedManagedUsers[$user['type']][(int) $user['id']])) {
                continue;
            }

            $selectedManagedUser[] = $allowedManagedUsers[$user['type']][(int) $user['id']];
        }

        return new ReferentMessage($referent, $selectedManagedUser);
    }

    private function createManagedUsersListIndexedByTypeAndId(Adherent $referent): array
    {
        $users = $this->usersListBuilder->createManagedUsersCollectionFor($referent);
        $registry = [];

        foreach ($users as $user) {
            if ($user->hasReferentsEmailsSubscription()) {
                $registry[$user->getType()][$user->getId()] = $user;
            }
        }

        return $registry;
    }
}
