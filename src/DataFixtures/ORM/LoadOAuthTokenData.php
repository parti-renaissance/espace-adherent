<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\OAuth\AccessToken;
use AppBundle\Entity\OAuth\AuthorizationCode;
use AppBundle\Entity\OAuth\Client;
use AppBundle\Entity\OAuth\RefreshToken;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\OAuth\ClientRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadOAuthTokenData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    public function load(ObjectManager $manager): void
    {
        $this->adherentRepository = $manager->getRepository(Adherent::class);
        $this->clientRepository = $manager->getRepository(Client::class);

        $authCode1 = $this->createAuthorizationCode(
            '673b3b128a9b5237b25a47e319e27d8c7d40255520269b3c382ea96012606f00d743927cf3af49f7',
            LoadAdherentData::ADHERENT_1_UUID,
            LoadClientData::CLIENT_01_UUID,
            '2017-08-02 16:39:18'
        );

        $authCode2 = $this->createAuthorizationCode(
            'aa56a0ab28aade7ef4a554adc02b297ebd4d5bfe587c6b847512b5f46c59cad26ce53766f8766248',
            LoadAdherentData::ADHERENT_1_UUID,
            LoadClientData::CLIENT_01_UUID,
            '+10 minutes'
        );
        $authCode2->revoke();

        $authCode3 = $this->createAuthorizationCode(
            '0c33b1711015b5e3d930f65b5dc87c398bfb3b29401028ee119c882bdf87cf9dcbf9a562629535e5',
            LoadAdherentData::ADHERENT_1_UUID,
            LoadClientData::CLIENT_02_UUID,
            '+10 minutes'
        );

        $accessToken1 = $this->createAccessToken(
            '491f7926e9c092894c9589a6740ceb402bcd4d2f38973623981b43e8fdacfd6f27bfbe6026e5d853',
            LoadAdherentData::ADHERENT_1_UUID,
            LoadClientData::CLIENT_01_UUID,
            '2017-08-03 16:06:11'
        );

        $accessToken2 = $this->createAccessToken(
            '4c843038f3d1ba017e6c835420efeefd03c024d9f413ecf96bc70acbdcb79e8ae0598a1579364190',
            LoadAdherentData::ADHERENT_1_UUID,
            LoadClientData::CLIENT_01_UUID,
            '+10 minutes'
        );
        $accessToken2->revoke();

        $refreshToken1 = $this->createRefreshToken(
            $accessToken1,
            'd03c024d9f413ecf96bc70acbdcb79e8ae0598a15793641904c843038f3d1ba017e6c835420efeef',
            '2017-08-03 16:13:24'
        );

        $refreshToken2 = $this->createRefreshToken(
            $accessToken1,
            'b3b29401028ee119c882bdf87cf9dcbf9a560c33b1711015b5e3d930f65b5dc87c398bf2629535e5',
            '+10 minutes'
        );
        $refreshToken2->revoke();

        $manager->persist($authCode1);
        $manager->persist($authCode2);
        $manager->persist($authCode3);
        $manager->persist($accessToken1);
        $manager->persist($accessToken2);
        $manager->persist($refreshToken1);
        $manager->persist($refreshToken2);
        $manager->flush();
    }

    private function createAuthorizationCode(
        string $identifier,
        string $userUuid,
        string $clientUuid,
        string $expiryDateTime
    ): AuthorizationCode {
        return new AuthorizationCode(
            Uuid::uuid5(Uuid::NAMESPACE_OID, $identifier),
            $this->adherentRepository->findByUuid(Uuid::fromString($userUuid)),
            $identifier,
            new \DateTime($expiryDateTime),
            'http://client-oauth.docker:8000/client/receive_authcode',
            $this->clientRepository->findClientByUuid(Uuid::fromString($clientUuid))
        );
    }

    private function createAccessToken(
        string $identifier,
        string $userUuid,
        string $clientUuid,
        string $expiryDateTime
    ): AccessToken {
        return new AccessToken(
            Uuid::uuid5(Uuid::NAMESPACE_OID, $identifier),
            $this->adherentRepository->findByUuid(Uuid::fromString($userUuid)),
            $identifier,
            new \DateTime($expiryDateTime),
            $this->clientRepository->findClientByUuid(Uuid::fromString($clientUuid))
        );
    }

    private function createRefreshToken(
        AccessToken $accessToken,
        string $identifier,
        string $expiryDateTime
    ): RefreshToken {
        return new RefreshToken(
            Uuid::uuid5(Uuid::NAMESPACE_OID, $identifier),
            $accessToken,
            $identifier,
            new \DateTime($expiryDateTime)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadClientData::class,
            LoadAdherentData::class,
        ];
    }
}
