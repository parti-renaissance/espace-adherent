<?php

namespace App\DataFixtures\ORM;

use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\CommitteeAdherentMessage;
use App\Entity\AdherentMessage\DeputyAdherentMessage;
use App\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use App\Entity\AdherentMessage\Filter\CommitteeFilter;
use App\Entity\AdherentMessage\Filter\ReferentUserFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\ReferentAdherentMessage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

class LoadAdherentMessageData extends Fixture implements DependentFixtureInterface
{
    public const MESSAGE_01_UUID = '969b1f08-53ec-4a7d-8d6e-7654a001b13f';
    public const MESSAGE_02_UUID = '65f6cdbf-0707-4940-86d8-cc1755aab17e';

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR_fr');

        // message draft
        $message1 = ReferentAdherentMessage::createFromAdherent(
            $this->getAuthor(ReferentAdherentMessage::class),
            Uuid::fromString(self::MESSAGE_01_UUID)
        );

        $message1->setContent($faker->randomHtml());
        $message1->setSubject($faker->sentence(5));
        $message1->setLabel($faker->sentence(2));

        if ($filter = $this->getFilter(ReferentAdherentMessage::class)) {
            $message1->setFilter($filter);
        }
        $message1->addMailchimpCampaign(new MailchimpCampaign($message1));
        $message1->setFilter(new ReferentUserFilter([$this->getReference('referent_tag_75')]));

        // message sent
        $message2 = ReferentAdherentMessage::createFromAdherent(
            $this->getAuthor(ReferentAdherentMessage::class),
            Uuid::fromString(self::MESSAGE_02_UUID)
        );

        $message2->setContent($faker->randomHtml());
        $message2->setSubject($faker->sentence(5));
        $message2->setLabel($faker->sentence(2));

        if ($filter = $this->getFilter(ReferentAdherentMessage::class)) {
            $message2->setFilter($filter);
        }
        $message2->addMailchimpCampaign(new MailchimpCampaign($message2));
        $message2->setFilter(new ReferentUserFilter([$this->getReference('referent_tag_75')]));
        $message2->markAsSent();

        $manager->persist($message1);
        $manager->persist($message2);
        $manager->flush();

        $message = null;
        foreach ($this->getMessageClasses() as $class) {
            for ($i = 1; $i <= 100; ++$i) {
                /** @var AdherentMessageInterface $message */
                /** @var AdherentMessageInterface $class */
                $message = $class::createFromAdherent($this->getAuthor($class));

                $message->setContent($faker->randomHtml());
                $message->setSubject($faker->sentence(5));
                $message->setLabel($faker->sentence(2));

                if ($filter = $this->getFilter($class)) {
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

    public function getDependencies()
    {
        return [
            LoadCommitteeData::class,
            LoadDistrictData::class,
            LoadReferentTagData::class,
        ];
    }

    private function getMessageClasses(): array
    {
        return [
            CommitteeAdherentMessage::class,
            ReferentAdherentMessage::class,
            DeputyAdherentMessage::class,
        ];
    }

    private function getAuthor(string $class): Adherent
    {
        switch ($class) {
            case ReferentAdherentMessage::class:
            case CommitteeAdherentMessage::class:
                return $this->getReference('adherent-8'); // referent@en-marche-dev.fr
            case DeputyAdherentMessage::class:
                return $this->getReference('deputy-75-1');
        }

        return $this->getReference('adherent-3');
    }

    private function getFilter($class): ?AdherentMessageFilterInterface
    {
        switch ($class) {
            case CommitteeAdherentMessage::class:
                return new CommitteeFilter($this->getReference('committee-10'));
            case DeputyAdherentMessage::class:
                return new AdherentZoneFilter(
                    $this
                        ->getReference('deputy-75-1')
                        ->getManagedDistrict()
                        ->getReferentTag()
                );
        }

        return null;
    }
}
