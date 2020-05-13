<?php

namespace App\Repository;

use App\Entity\FacebookProfile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FacebookProfileRepository extends ServiceEntityRepository
{
    use UuidEntityRepositoryTrait {
        findOneByUuid as findOneByValidUuid;
    }

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, FacebookProfile::class);
    }

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
