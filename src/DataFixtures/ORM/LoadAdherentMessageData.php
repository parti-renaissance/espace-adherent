<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;
use AppBundle\Entity\AdherentMessage\CitizenProjectAdherentMessage;
use AppBundle\Entity\AdherentMessage\CommitteeAdherentMessage;
use AppBundle\Entity\AdherentMessage\DeputyAdherentMessage;
use AppBundle\Entity\AdherentMessage\Filter\AdherentZoneFilter;
use AppBundle\Entity\AdherentMessage\Filter\CitizenProjectFilter;
use AppBundle\Entity\AdherentMessage\Filter\CommitteeFilter;
use AppBundle\Entity\AdherentMessage\ReferentAdherentMessage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class LoadAdherentMessageData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR_fr');

        foreach ($this->getMessageClasses() as $class) {
            /** @var AdherentMessageInterface $messageCompleted */
            $messageCompleted = $class::createFromAdherent($this->getAuthor($class));
            $messageCompleted->setContent($faker->randomHtml());
            $className = substr(strrchr($class, '\\'), 1);
            $messageCompleted->setSubject("Synchronized $className message with externalId");
            $messageCompleted->setLabel($faker->sentence(2));
            $messageCompleted->setExternalId('123abc');
            $messageCompleted->setSynchronized(true);

            if ($filter = $this->getFilter($class)) {
                $messageCompleted->setFilter($filter);
            }

            $manager->persist($messageCompleted);

            for ($i = 1; $i <= 100; ++$i) {
                /** @var AdherentMessageInterface $message */
                $message = $class::createFromAdherent($this->getAuthor($class));

                $message->setContent($faker->randomHtml());
                $message->setSubject($faker->sentence(5));
                $message->setLabel($faker->sentence(2));

                if ($filter = $this->getFilter($class)) {
                    $message->setFilter($filter);
                }

                $manager->persist($message);
            }
            $manager->flush();
        }
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
        ];
    }

    private function getMessageClasses(): array
    {
        return [
            CommitteeAdherentMessage::class,
            ReferentAdherentMessage::class,
            DeputyAdherentMessage::class,
            CitizenProjectAdherentMessage::class,
        ];
    }

    private function getAuthor(string $class): Adherent
    {
        switch ($class) {
            case ReferentAdherentMessage::class: return $this->getReference('adherent-8');
            case CommitteeAdherentMessage::class: return $this->getReference('adherent-8');
            case DeputyAdherentMessage::class: return $this->getReference('deputy-75-1');
            case CitizenProjectAdherentMessage::class: return $this->getReference('adherent-3');
        }
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
            case CitizenProjectAdherentMessage::class:
                return new CitizenProjectFilter($this->getReference('citizen-project-3'));
        }

        return null;
    }
}
