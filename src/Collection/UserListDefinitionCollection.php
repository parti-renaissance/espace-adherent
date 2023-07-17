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
            default:
                throw new \InvalidArgumentException($type.' is not valid.');
        }
    }

    public function toString(): string
    {
        return implode(', ', $this->map(static function (UserListDefinition $userListDefinition) {
            return $userListDefinition->getLabel();
        })->toArray());
    }
}
