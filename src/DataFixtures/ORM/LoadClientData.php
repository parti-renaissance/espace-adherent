<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\OAuth\Client;
use AppBundle\OAuth\Model\GrantTypeEnum;
use AppBundle\OAuth\Model\Scope;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadClientData extends AbstractFixture implements FixtureInterface
{
    use ContainerAwareTrait;

    public const CLIENT_01_UUID = 'f80ce2df-af6d-4ce4-8239-04cfcefd5a19';
    public const CLIENT_02_UUID = '661cc3b7-322d-4441-a510-ab04eda71737';
    public const CLIENT_03_UUID = '4122f4ce-f994-45f7-9ff5-f9f09ab3991e';
    public const CLIENT_04_UUID = '4222f4ce-f994-45f7-9ff5-f9f09ab3991f';
    public const CLIENT_05_UUID = '4222f4ce-f994-45f7-9ff5-f9f09ab3992e';
    public const CLIENT_06_UUID = '4f3394d4-7137-424a-8c73-27e0ad641fc9';
    public const CLIENT_07_UUID = 'e7c07c65-bba1-4a05-8d9b-76de6e5145c6';
    public const CLIENT_08_UUID = '4222f4ce-f994-45f7-9ff5-f9f09ab3992b';
    public const CLIENT_09_UUID = '40bdd6db-e422-4153-819c-9973c09f9297';

    public function load(ObjectManager $manager)
    {
        $client1 = new Client(
            Uuid::fromString(self::CLIENT_01_UUID),
            'En-Marche !',
            'Plateforme Citoyenne de la République En-Marche !',
            '2x26pszrpag408so88w4wwo4gs8o8ok4osskcw00ow80sgkkcs',
            [GrantTypeEnum::AUTHORIZATION_CODE, GrantTypeEnum::REFRESH_TOKEN],
            ['http://client-oauth.docker:8000/client/receive_authcode', 'https://en-marche.fr/callback']
        );
        $manager->persist($client1);

        $client2 = new Client(
            Uuid::fromString(self::CLIENT_02_UUID),
            'En-Marche (avec by pass auth) !',
            'Plateforme Citoyenne de la République En-Marche !',
            'y866p4gbcbrsl84ptnhas7751iw3on319983a13e6y862tb9c2',
            [GrantTypeEnum::AUTHORIZATION_CODE, GrantTypeEnum::REFRESH_TOKEN],
            ['http://client-oauth.docker:8000/client/receive_authcode']
        );
        $client2->setAskUserForAuthorization(false);
        $manager->persist($client2);

        $client3 = new Client(
            Uuid::fromString(self::CLIENT_03_UUID),
            'En-Marche API !',
            'Plateforme Citoyenne de la République En-Marche !',
            'dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA=',
            [GrantTypeEnum::CLIENT_CREDENTIALS]
        );
        $client3->addSupportedScope(Scope::READ_USERS);
        $manager->persist($client3);

        $client4 = new Client(
            Uuid::fromString(self::CLIENT_04_UUID),
            'Client Web Hook 1',
            'Plateforme Citoyenne de la République En-Marche !',
            'dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx1omA=',
            [GrantTypeEnum::CLIENT_CREDENTIALS]
        );
        $client4->addSupportedScope(Scope::WEB_HOOK);
        $client4->addSupportedScope(Scope::READ_USERS);
        $manager->persist($client4);
        $this->setReference('web_hook_client_1', $client4);

        $client5 = new Client(
            Uuid::fromString(self::CLIENT_05_UUID),
            'Client Web Hook 2',
            'Plateforme Citoyenne de la République En-Marche !',
            'dALH/khq9BcjOS0GB6u5NaJ3R9k2yvSBq5wYUHx12T8=',
            [GrantTypeEnum::CLIENT_CREDENTIALS]
        );
        $client5->addSupportedScope(Scope::WEB_HOOK);
        $client5->addSupportedScope(Scope::READ_USERS);
        $client5->addSupportedScope(Scope::WRITE_USERS);
        $manager->persist($client5);
        $this->setReference('web_hook_client_2', $client5);

        $manager->flush();

        $client6 = new Client(
            Uuid::fromString(self::CLIENT_06_UUID),
            'Data En Marche',
            'Pour récupérer des données de typeforms par data-api.en-marche.fr!',
            'crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE=',
            [GrantTypeEnum::CLIENT_CREDENTIALS]
        );
        $client6->addSupportedScope(Scope::READ_TYPEFORMS);
        $client6->addSupportedScope(Scope::READ_STATS);
        $manager->persist($client6);

        $manager->flush();

        $client7 = new Client(
            Uuid::fromString(self::CLIENT_07_UUID),
            'API En-Marche !',
            'Plateforme Citoyenne de la République En-Marche !',
            'KsvrVu9maHRW21eiOsWVuUYC//zaglQw0s60NIj3TbA=',
            [GrantTypeEnum::CLIENT_CREDENTIALS]
        );
        $manager->persist($client7);
        $this->setReference('web_hook_client_api', $client7);

        $client8 = new Client(
            Uuid::fromString(self::CLIENT_08_UUID),
            'J\'écoute',
            'J\'écoute',
            '4THZGbOfHJvRHk8bHdtZP3BTrMWFod6bOZb2mY3wLE=',
            [GrantTypeEnum::AUTHORIZATION_CODE, GrantTypeEnum::REFRESH_TOKEN],
            ['http://client-oauth.docker:8000/client/receive_authcode']
        );
        $client8->setAskUserForAuthorization(false);
        $client8->addSupportedScope(Scope::JECOUTE_SURVEYS);

        $manager->persist($client8);

        $manager->flush();

        $client9 = new Client(
            Uuid::fromString(self::CLIENT_09_UUID),
            'CRM Paris',
            'Pour récupérer des données sur les adhérents de Paris.',
            'cChiFrOxtYb4CgnKoYvV9evEcrOsk2hb9wvO73QLYyc=',
            [GrantTypeEnum::CLIENT_CREDENTIALS]
        );
        $client9->addSupportedScope(Scope::CRM_PARIS);
        $manager->persist($client9);

        $manager->flush();
    }
}
