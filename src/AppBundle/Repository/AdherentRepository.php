<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use Doctrine\ORM\EntityRepository;

class AdherentRepository extends EntityRepository
{
    /**
     * Finds an Adherent instance by its email address.
     *
     * @param string $email
     *
     * @return Adherent|null
     */
    public function findByEmail(string $email)
    {
        return $this->findOneBy(['emailAddress' => $email]);
    }

    /**
     * Finds an Adherent instance by its unique UUID.
     *
     * @param string $uuid
     *
     * @return Adherent|null
     */
    public function findByUuid(string $uuid)
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
