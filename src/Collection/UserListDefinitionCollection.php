<?php

namespace App\Collection;

use App\Entity\UserListDefinition;
use App\Entity\UserListDefinitionEnum;
use Doctrine\Common\Collections\ArrayCollection;

class UserListDefinitionCollection extends ArrayCollection
{
    public function forType(string $type): self
    {
        switch ($type) {
            case 'referent':
            case 'deputy':
            case 'senator':
            case 'senatorial_candidate':
                return $this->filter(function (UserListDefinition $userListDefinition) {
                    return UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE === $userListDefinition->getType() || \in_array($userListDefinition->getCode(), ['lre', 'la_republique_ensemble'], true);
                });
            case 'lre':
                return $this->filter(function (UserListDefinition $userListDefinition) {
                    return \in_array($userListDefinition->getType(), [UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE, UserListDefinitionEnum::TYPE_LRE], true);
                });
            default:
                throw new \InvalidArgumentException($type.' is not valid.');
        }
    }
}
