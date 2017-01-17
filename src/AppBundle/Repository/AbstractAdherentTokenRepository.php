<?php

namespace AppBundle\Repository;

use AppBundle\Entity\AdherentExpirableTokenInterface;
use AppBundle\ValueObject\SHA1;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Uuid;

class AbstractAdherentTokenRepository extends EntityRepository
{
    /**
     * Returns the most recent token of an adherent.
     *
     * @param string $adherent
     *
     * @return AdherentExpirableTokenInterface|null
     */
    public function findAdherentMostRecentKey(string $adherent)
    {
        $adherent = Uuid::fromString($adherent);

        $query = $this
            ->createQueryBuilder('t')
            ->where('t.adherentUuid = :uuid')
            ->orderBy('t.createdAt', 'DESC')
            ->setParameter('uuid', $adherent->toString())
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Finds a AdherentToken instance by its token unique value.
     *
     * @param string $token
     *
     * @return AdherentExpirableTokenInterface|null
     */
    public function findByToken(string $token)
    {
        $token = SHA1::fromString($token);

        return $this->findOneBy(['value' => $token->getHash()]);
    }
}
