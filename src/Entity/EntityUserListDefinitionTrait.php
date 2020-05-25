<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait EntityUserListDefinitionTrait
{
    /**
     * @var Collection|UserListDefinition[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\UserListDefinition")
     */
    protected $userListDefinitions;

    /**
     * @return Collection|UserListDefinition[]
     */
    public function getUserListDefinitions(): Collection
    {
        return $this->userListDefinitions;
    }

    public function addUserListDefinition(UserListDefinition $userListDefinition): void
    {
        if (!$this->userListDefinitions->contains($userListDefinition)) {
            $this->userListDefinitions->add($userListDefinition);
        }
    }

    public function removeUserListDefinition(UserListDefinition $userListDefinition): void
    {
        $this->userListDefinitions->remove($userListDefinition);
    }
}
