<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;

class ReferentMessageFactory
{
    /**
     * @var DataGridFactory
     */
    private $dataGridFactory;

    public function __construct(DataGridFactory $dataGridFactory)
    {
        $this->dataGridFactory = $dataGridFactory;
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
        $users = $this->dataGridFactory->findUsersManagedBy($referent);
        $registry = [];

        foreach ($users as $user) {
            if ($user->hasReferentsEmailsSubscription()) {
                $registry[$user->getType()][$user->getId()] = $user;
            }
        }

        return $registry;
    }
}
