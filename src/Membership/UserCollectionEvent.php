<?php

namespace AppBundle\Membership;

use AppBundle\Entity\Adherent;
use Symfony\Component\EventDispatcher\Event;

class UserCollectionEvent extends Event implements UserEventInterface
{
    private $users;

    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * @return Adherent[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }
}
