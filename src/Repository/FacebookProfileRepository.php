<?php

namespace AppBundle\Repository;

use AppBundle\Entity\FacebookProfile;
use Doctrine\ORM\EntityRepository;

class FacebookProfileRepository extends EntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUuid(?string $uuid): ?FacebookProfile
    {
        return $this->findOneByValidUuid($uuid);
    }

    public function persistFromSDKResponse(string $accessToken, array $data): FacebookProfile
    {
        if (!$fbProfile = $this->findOneByUuid(FacebookProfile::createUuid($data['id']))) {
            $fbProfile = FacebookProfile::createFromSDKResponse($accessToken, $data);
        }

        $fbProfile->setUpdatedAt(new \DateTime());

        $this->_em->persist($fbProfile);
        $this->_em->flush();

        return $fbProfile;
    }
}
