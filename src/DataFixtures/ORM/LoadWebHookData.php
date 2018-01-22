<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\WebHook\Callback;
use AppBundle\Entity\WebHook\WebHook;
use AppBundle\WebHook\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadWebHookData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $webHook1 = new WebHook(
            Event::USER_DELETION(),
            [
                new Callback(
                    $this->getReference('web_hook_client_1'),
                    [
                        'http://test.com/awesome',
                        'https://www.en-marche.fr/webhook/endpoint',
                    ]
                ),
            ]
        );
        $manager->persist($webHook1);

        $webHook2 = new WebHook(
            Event::USER_MODIFICATION(),
            [
                new Callback(
                    $this->getReference('web_hook_client_1'),
                    [
                        'http://test.com/awesome',
                        'https://www.en-marche.fr/webhook/endpoint',
                    ]
                ),
                new Callback(
                    $this->getReference('web_hook_client_2'),
                    [
                        'http://client5.com/web_hook',
                    ]
                ),
            ]
        );
        $manager->persist($webHook2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [LoadClientData::class];
    }
}
