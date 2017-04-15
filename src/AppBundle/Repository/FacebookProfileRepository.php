<?php

namespace AppBundle\Repository;

use AppBundle\Entity\FacebookProfile;
use Doctrine\ORM\EntityRepository;

class FacebookProfileRepository extends EntityRepository
{
    public function persistFromSDKResponse(array $data): FacebookProfile
    {
        if ($fbProfile = $this->findOneBy(['uuid' => FacebookProfile::createUuid($data['id'])])) {
            return $fbProfile;
        }

        $fbProfile = FacebookProfile::createFromSDKResponse($data);

        $this->_em->persist($fbProfile);
        $this->_em->flush();

        return $fbProfile;
    }
}
