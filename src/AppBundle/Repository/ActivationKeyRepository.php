<?php

namespace AppBundle\Repository;

use AppBundle\Entity\ActivationKey;
use AppBundle\ValueObject\SHA1;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Uuid;

class ActivationKeyRepository extends EntityRepository
{
    /**
     * Returns the most recent activation key of an adherent.
     *
     * @param string $adherent
     *
     * @return ActivationKey|null
     */
    public function findAdherentMostRecentKey(string $adherent)
    {
        $adherent = Uuid::fromString($adherent);

        $query = $this
            ->createQueryBuilder('a')
            ->where('a.adherentUuid = :uuid')
            ->orderBy('a.createdAt', 'DESC')
            ->setParameter('uuid', $adherent->toString())
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Finds an ActivationKey instance by its token unique value.
     *
     * @param string $token
     *
     * @return ActivationKey|null
     */
    public function findByToken(string $token)
    {
        $token = SHA1::fromString($token);

        return $this->findOneBy(['token' => $token->getHash()]);
    }
}
