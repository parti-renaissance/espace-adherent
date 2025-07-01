<?php

namespace App\DataFixtures\ORM;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\AdherentMessage\Listener\AdherentMessageChangeSubscriber;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\Filter\MessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\PresidentDepartmentalAssemblyAdherentMessage;
use App\Entity\AdherentMessage\SenatorAdherentMessage;
use App\Entity\AdherentMessage\StatutoryAdherentMessage;
use App\Entity\Committee;
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

        $manager->persist($message = StatutoryAdherentMessage::createFromAdherent($this->getAuthor(StatutoryAdherentMessage::class)));
        $message->setSource(AdherentMessageInterface::SOURCE_VOX);
        $message->setRecipientCount(10);
        $message->setFilter(new MessageFilter([$parisZone]));
        $message->setContent($faker->randomHtml());
        $message->setSubject($faker->sentence(5));
        $message->setLabel($faker->sentence(2));

        // message draft
        $message1 = PresidentDepartmentalAssemblyAdherentMessage::createFromAdherent(
            $this->getAuthor(PresidentDepartmentalAssemblyAdherentMessage::class),
            Uuid::fromString(self::MESSAGE_01_UUID)
        );

        $message1->setContent($faker->randomHtml());
        $message1->setSubject($faker->sentence(5));
        $message1->setLabel($faker->sentence(2));

        $message1->addMailchimpCampaign(new MailchimpCampaign($message1));
        $message1->setFilter(new MessageFilter([$parisZone = LoadGeoZoneData::getZoneReference($manager, 'zone_department_75')]));

        // message sent
        $message2 = PresidentDepartmentalAssemblyAdherentMessage::createFromAdherent(
            $this->getAuthor(PresidentDepartmentalAssemblyAdherentMessage::class),
            Uuid::fromString(self::MESSAGE_02_UUID)
        );

        $message2->setContent($faker->randomHtml());
        $message2->setSubject($faker->sentence(5));
        $message2->setLabel($faker->sentence(2));

        $message2->addMailchimpCampaign(new MailchimpCampaign($message2));
        $message2->setFilter(new MessageFilter([$parisZone]));
        $message2->markAsSent();

        $manager->persist($message1);
        $manager->persist($message2);

        $manager->persist($message = StatutoryAdherentMessage::createFromAdherent($this->getAuthor(StatutoryAdherentMessage::class)));
        $message->setSource(AdherentMessageInterface::SOURCE_VOX);
        $message->setRecipientCount(2);
        $message->setFilter(new MessageFilter([$parisZone]));
        $message->markAsSent();
        $message->setContent($faker->randomHtml());
        $message->setSubject($faker->sentence(5));
        $message->setLabel($faker->sentence(2));

        $manager->flush();

        foreach ($this->getMessageClasses() as $class) {
            for ($i = 1; $i <= 100; ++$i) {
                /** @var AdherentMessageInterface $message */
                /** @var AdherentMessageInterface $class */
                $message = $class::createFromAdherent($this->getAuthor($class));

                $message->setContent($faker->randomHtml());
                $message->setSubject($faker->sentence(5));
                $message->setLabel($faker->sentence(2));

                if ($filter = $this->getFilter($manager, $class)) {
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

    private function getMessageClasses(): array
    {
        return [
            CommitteeAdherentMessage::class,
            DeputyAdherentMessage::class,
            SenatorAdherentMessage::class,
            PresidentDepartmentalAssemblyAdherentMessage::class,
        ];
    }

    private function getAuthor(string $class): Adherent
    {
        switch ($class) {
            case PresidentDepartmentalAssemblyAdherentMessage::class:
            case CommitteeAdherentMessage::class:
                return $this->getReference('adherent-8', Adherent::class); // referent@en-marche-dev.fr
            case DeputyAdherentMessage::class:
                return $this->getReference('deputy-75-1', Adherent::class);
            case SenatorAdherentMessage::class:
                return $this->getReference('senator-59', Adherent::class);
            case StatutoryAdherentMessage::class:
                return $this->getReference('president-ad-1', Adherent::class);
        }

        return $this->getReference('adherent-3', Adherent::class);
    }

    private function getFilter(ObjectManager $manager, string $class): ?AdherentMessageFilterInterface
    {
        switch ($class) {
            case CommitteeAdherentMessage::class:
                $filter = new MessageFilter();
                $filter->setCommittee($this->getReference('committee-10', Committee::class));

                return $filter;
            case DeputyAdherentMessage::class:
                return new MessageFilter([LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1')]);
        }

        return null;
    }
}
