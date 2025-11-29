<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\AdherentMessage\Listener\AdherentMessageChangeSubscriber;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\Committee;
use App\Scope\ScopeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class LoadAdherentMessageData extends Fixture implements DependentFixtureInterface
{
    public const MESSAGE_01_UUID = '969b1f08-53ec-4a7d-8d6e-7654a001b13f';
    public const MESSAGE_02_UUID = '65f6cdbf-0707-4940-86d8-cc1755aab17e';
    public const MESSAGE_03_UUID = '75f6cdbf-0707-4940-86d8-cc1755aab17e';

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $eventManager = $manager->getEventManager();
        $listener = current(array_filter($eventManager->getListeners(Events::postFlush), function ($listener) {
            return $listener instanceof AdherentMessageChangeSubscriber;
        }));
        $eventManager->removeEventSubscriber($listener);

        $faker = Factory::create('FR_fr');

        $parisZone = LoadGeoZoneData::getZoneReference($manager, 'zone_department_75');

        $manager->persist($message = AdherentMessage::createFromAdherent($this->getAuthor('', true)));
        $message->setIsStatutory(true);
        $message->setInstanceScope(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $message->setSource(AdherentMessageInterface::SOURCE_CADRE);
        $message->setRecipientCount(10);
        $message->setFilter(new MessageFilter([$parisZone]));
        $message->setContent($faker->randomHtml());
        $message->setSubject($faker->sentence(5));
        $message->setLabel($faker->sentence(2));

        // message draft
        $message1 = AdherentMessage::createFromAdherent(
            $author = $this->getAuthor(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY),
            Uuid::fromString(self::MESSAGE_01_UUID)
        );
        $message1->teamOwner = $author;
        $message1->setSender($author);
        $message1->setInstanceScope(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $message1->setContent($faker->randomHtml());
        $message1->setSubject($faker->sentence(5));
        $message1->setLabel($faker->sentence(2));

        $message1->addMailchimpCampaign(new MailchimpCampaign($message1));
        $message1->setFilter(new AudienceFilter([$parisZone = LoadGeoZoneData::getZoneReference($manager, 'zone_department_92')]));

        // message sent
        $message2 = AdherentMessage::createFromAdherent(
            $pad = $this->getAuthor(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY),
            Uuid::fromString(self::MESSAGE_02_UUID)
        );

        $message2->setInstanceScope(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $message2->setContent($faker->randomHtml());
        $message2->setSubject($faker->sentence(5));
        $message2->setLabel($faker->sentence(2));

        $message2->addMailchimpCampaign(new MailchimpCampaign($message2));
        $message2->setFilter(new AudienceFilter([$parisZone]));
        $message2->markAsSent();

        $manager->persist($message1);
        $manager->persist($message2);

        $manager->persist($message = AdherentMessage::createFromAdherent($pad, Uuid::fromString(self::MESSAGE_03_UUID)));
        $message->setInstanceScope(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $message->setSource(AdherentMessageInterface::SOURCE_VOX);
        $message->setRecipientCount(2);
        $message->setFilter(new AudienceFilter([$parisZone]));
        $message->markAsSent();
        $message->setContent($faker->randomHtml());
        $message->setSubject($faker->sentence(5));
        $message->setLabel($faker->sentence(2));

        $manager->persist($message = AdherentMessage::createFromAdherent($this->getAuthor('', true)));
        $message->setIsStatutory(true);
        $message->setInstanceScope(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY);
        $message->setSource(AdherentMessageInterface::SOURCE_CADRE);
        $message->setRecipientCount(2);
        $message->setFilter(new AudienceFilter([$parisZone]));
        $message->markAsSent();
        $message->setContent($faker->randomHtml());
        $message->setSubject($faker->sentence(5));
        $message->setLabel($faker->sentence(2));

        $manager->flush();

        foreach ($this->getInstanceScopes() as $instanceScope) {
            for ($i = 1; $i <= 100; ++$i) {
                $message = AdherentMessage::createFromAdherent($this->getAuthor($instanceScope));
                $message->setInstanceScope($instanceScope);
                $message->setContent($faker->randomHtml());
                $message->setSubject($faker->sentence(5));
                $message->setLabel($faker->sentence(2));

                if ($filter = $this->getFilter($manager, $instanceScope)) {
                    $message->setFilter($filter);
                }
                $message->addMailchimpCampaign(new MailchimpCampaign($message));
                $message->getMailchimpCampaigns()[0]->setSynchronized(true);

                $manager->persist($message);
            }
            $message->markAsSent();

            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [
            LoadCommitteeV1Data::class,
            LoadGeoZoneData::class,
        ];
    }

    private function getInstanceScopes(): array
    {
        return [
            ScopeEnum::ANIMATOR,
            ScopeEnum::DEPUTY,
            ScopeEnum::SENATOR,
            ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
        ];
    }

    private function getAuthor(string $instanceScope, bool $isStaturory = false): Adherent
    {
        if ($isStaturory) {
            return $this->getReference('president-ad-1', Adherent::class);
        }

        switch ($instanceScope) {
            case ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY:
            case ScopeEnum::ANIMATOR:
                return $this->getReference('adherent-8', Adherent::class); // referent@en-marche-dev.fr
            case ScopeEnum::DEPUTY:
                return $this->getReference('deputy-75-1', Adherent::class);
            case ScopeEnum::SENATOR:
                return $this->getReference('senator-59', Adherent::class);
        }

        return $this->getReference('adherent-3', Adherent::class);
    }

    private function getFilter(ObjectManager $manager, string $instanceScope): ?AdherentMessageFilterInterface
    {
        switch ($instanceScope) {
            case ScopeEnum::ANIMATOR:
                $filter = new AudienceFilter();
                $filter->setCommittee($this->getReference('committee-10', Committee::class));

                return $filter;
            case ScopeEnum::DEPUTY:
                return new AudienceFilter([LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')]);
        }

        return null;
    }
}
