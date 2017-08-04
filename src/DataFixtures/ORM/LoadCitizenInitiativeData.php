<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CoachingRequest;
use AppBundle\Entity\EventCategory;
use AppBundle\Entity\Skill;
use AppBundle\Event\EventFactory;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Event\EventRegistrationFactory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCitizenInitiativeData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, OrderedFixtureInterface
{
    const CITIZEN_INITIATIVE_1_UUID = '5b603c20-09e3-4c7d-8da1-f326f436e208';
    const CITIZEN_INITIATIVE_2_UUID = '49d77390-ca68-49b7-ad56-bb0f31dc30b9';
    const CITIZEN_INITIATIVE_3_UUID = '46ba94dc-b1ff-4807-b8a3-db7366ec805f';
    const CITIZEN_INITIATIVE_4_UUID = 'c55a4d71-f0df-4ab8-a03a-fe8d9d3345ff';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $eventCategory1 = $manager->getRepository(EventCategory::class)->findOneBy(['name' => LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE007']]);
        $eventCategory2 = $manager->getRepository(EventCategory::class)->findOneBy(['name' => LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE008']]);
        $eventCategory3 = $manager->getRepository(EventCategory::class)->findOneBy(['name' => LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE009']]);

        $author1 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $author2 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $author3 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_3_UUID);

        $committeeEventFactory = $this->getEventFactory();
        $registrationFactory = $this->getEventRegistrationFactory();

        $initiative1 = $committeeEventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_1_UUID,
            'organizer' => $this->getReference('adherent-1'),
            'name' => 'Apprenez à sauver des vies',
            'category' => $eventCategory1,
            'description' => 'Venez vous initier aux gestes qui sauvent',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '30 Zeppelinstrasse', 47.3950062, 8.53838),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 19:00:00'),
            'expert_assistance_needed' => true,
            'expert_assistance_description' => 'Besoin quelqu\'un de la Croix ROuge',
            'coaching_requested' => false,
        ]);
        $initiative1->setInterests(['sante']);
        $initiative1->incrementParticipantsCount();

        $initiative2 = $committeeEventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_2_UUID,
            'organizer' => $this->getReference('adherent-2'),
            'committee' => $eventCategory2,
            'name' => 'Apprenez à sauver des vies',
            'category' => $eventCategory1,
            'description' => 'Venez vous initier aux gestes qui sauvent',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', 45.7218703, 5.929463),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+9 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+ 9 days')).' 19:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
        ]);
        $initiative2->incrementParticipantsCount();
        $initiative2->setInterests(['sante']);

        $initiative3 = $committeeEventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_3_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Apprenez à sauver des vies',
            'category' => $eventCategory3,
            'description' => 'Venez vous initier aux gestes qui sauvent',
            'address' => PostAddress::createFrenchAddress('16 rue de la Paix', '75008-75108', 48.869331, 2.331595),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 16:00:00'),
            'expert_assistance_needed' => true,
            'expert_assistance_description' => 'J’ai besoin d’aide pour cet événement. Besoin de quelqu’un ayant le permis poids lourd pour pouvoir
transporter tous les déchets lourds trouvés.',
            'coaching_requested' => true,
        ]);
        $initiative3->incrementParticipantsCount();
        $initiative3->setInterests(['sante', 'securite']);
        $initiative3->setCoachingRequest(new CoachingRequest('Description du besoin', 'Ma solution proposée', 'Les moyens nécessaires'));

        $initiative4 = $committeeEventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_4_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Nettoyage de la ville',
            'category' => $eventCategory1,
            'description' => 'Nous allons rendre notre ville propre',
            'address' => PostAddress::createFrenchAddress('26 rue de la Paix', '75008-75108', 48.869878, 2.332197),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 10:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 15:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
        ]);
        $initiative4->incrementParticipantsCount();
        $initiative4->setInterests(['environement', 'territoire']);

        foreach ($manager->getRepository(Skill::class)->findBy(['name' => LoadSkillData::SKILLS['S009']]) as $skill) {
            $initiative1->addSkill($skill);
        }

        foreach ($manager->getRepository(Skill::class)->findBy(['name' => LoadSkillData::SKILLS['S009']]) as $skill) {
            $initiative2->addSkill($skill);
        }

        foreach ($manager->getRepository(Skill::class)->findBy(['name' => [
            LoadSkillData::SKILLS['S009'],
            LoadSkillData::SKILLS['S010'],
            LoadSkillData::SKILLS['S011'], ],
        ]) as $skill) {
            $initiative3->addSkill($skill);
        }

        foreach ($manager->getRepository(Skill::class)->findBy(['name' => [
            LoadSkillData::SKILLS['S002'],
            LoadSkillData::SKILLS['S006'],
            LoadSkillData::SKILLS['S011'], ],
        ]) as $skill) {
            $initiative4->addSkill($skill);
        }

        $manager->persist($initiative1);
        $manager->persist($initiative2);
        $manager->persist($initiative3);
        $manager->persist($initiative4);

        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative1, $author1)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative2, $author2)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative3, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative4, $author3)));

        $manager->flush();
    }

    private function getEventFactory(): EventFactory
    {
        return $this->container->get('app.event.factory');
    }

    private function getEventRegistrationFactory(): EventRegistrationFactory
    {
        return $this->container->get('app.event.registration_factory');
    }

    public function getOrder()
    {
        return 2;
    }
}
