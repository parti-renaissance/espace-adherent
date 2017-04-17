<?php

namespace AppBundle\Repository;

use AppBundle\Entity\FacebookProfile;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Uuid;

class FacebookProfileRepository extends EntityRepository
{
    public function findOneByUuid(?string $uuid): ?FacebookProfile
    {
        if (!$uuid || !Uuid::isValid($uuid)) {
            return null;
        }

        return $this->findOneBy(['uuid' => $uuid]);
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
