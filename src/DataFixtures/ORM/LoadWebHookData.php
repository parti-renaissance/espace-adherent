<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\WebHook\Callback;
use AppBundle\Entity\WebHook\WebHook;
use AppBundle\WebHook\Event;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadWebHookData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

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
