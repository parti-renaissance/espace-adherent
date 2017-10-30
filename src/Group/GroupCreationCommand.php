<?php

namespace AppBundle\Group;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use AppBundle\Validator\UniqueGroup as AssertUniqueGroup;

/**
 * @AssertUniqueGroup
 */
class GroupCreationCommand extends GroupCommand
{
    /** @var Adherent */
    private $adherent;

    protected function __construct(Address $address = null)
    {
        parent::__construct($address);
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        $dto = new self();
        $dto->adherent = $adherent;
        $dto->phone = $adherent->getPhone();

        return $dto;
    }

    public function getAdherent(): Adherent
    {
        return $this->adherent;
    }

    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }
}
