<?php

namespace App\Entity;

use App\Collection\UserListDefinitionCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait EntityUserListDefinitionTrait
{
    /**
     * @var Collection|UserListDefinition[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\UserListDefinition", cascade={"persist"})
     */
    protected $userListDefinitions;

    /**
     * @return Collection|UserListDefinition[]
     */
    public function getUserListDefinitions(): Collection
    {
        if ($this->userListDefinitions instanceof UserListDefinitionCollection) {
            return $this->userListDefinitions;
        }

        return new UserListDefinitionCollection($this->userListDefinitions->toArray());
    }

    public function addUserListDefinition(UserListDefinition $userListDefinition): void
    {
        if (!$this->userListDefinitions->contains($userListDefinition)) {
            $this->userListDefinitions->add($userListDefinition);
        }
    }

    public function removeUserListDefinition(UserListDefinition $userListDefinition): void
    {
        $this->userListDefinitions->removeElement($userListDefinition);
    }
}
