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

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR_fr');

        $message = ReferentAdherentMessage::createFromAdherent(
            $this->getAuthor(ReferentAdherentMessage::class),
            Uuid::fromString(self::MESSAGE_01_UUID)
        );

        $message->setContent($faker->randomHtml());
        $message->setSubject($faker->sentence(5));
        $message->setLabel($faker->sentence(2));

        if ($filter = $this->getFilter(ReferentAdherentMessage::class)) {
            $message->setFilter($filter);
        }
        $message->addMailchimpCampaign(new MailchimpCampaign($message));
        $message->setFilter(new ReferentUserFilter([$this->getReference('referent_tag_75')]));

        $manager->persist($message);
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
