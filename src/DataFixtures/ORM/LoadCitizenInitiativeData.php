<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CoachingRequest;
use AppBundle\Entity\CitizenInitiativeCategory;
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
    const CITIZEN_INITIATIVE_5_UUID = '81aaee46-5308-477c-9960-9731fb6898c3';
    const CITIZEN_INITIATIVE_6_UUID = '8988a5ce-9e43-4d34-b070-4d404688ff96';
    const CITIZEN_INITIATIVE_7_UUID = '4f989ee6-ebd3-4544-b73f-a5262340aa38';
    const CITIZEN_INITIATIVE_8_UUID = 'ba20b8b7-0f7b-4821-8b52-ab829f67c871';
    const CITIZEN_INITIATIVE_9_UUID = '09722a7c-a09a-4fd8-9bac-4d6a7313947a';

    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $category1 = $manager->getRepository(CitizenInitiativeCategory::class)->findOneBy(['name' => LoadCitizenInitiativeCategoryData::CITIZEN_INITIATIVE_CATEGORIES['CIC007']]);
        $category2 = $manager->getRepository(CitizenInitiativeCategory::class)->findOneBy(['name' => LoadCitizenInitiativeCategoryData::CITIZEN_INITIATIVE_CATEGORIES['CIC008']]);
        $category3 = $manager->getRepository(CitizenInitiativeCategory::class)->findOneBy(['name' => LoadCitizenInitiativeCategoryData::CITIZEN_INITIATIVE_CATEGORIES['CIC009']]);

        $author1 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $author2 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $author3 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $author13 = $manager->getRepository(Adherent::class)->findByUuid(LoadAdherentData::ADHERENT_13_UUID);

        $eventFactory = $this->getEventFactory();
        $registrationFactory = $this->getEventRegistrationFactory();

        $initiative1 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_1_UUID,
            'organizer' => $this->getReference('adherent-1'),
            'name' => 'Apprenez à sauver des vies',
            'category' => $category1,
            'description' => 'Venez vous initier aux gestes qui sauvent',
            'address' => PostAddress::createForeignAddress('CH', '8057', 'Zürich', '30 Zeppelinstrasse', 47.3950062, 8.53838),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+3 days')).' 19:00:00'),
            'expert_assistance_needed' => true,
            'expert_assistance_description' => 'Besoin quelqu\'un de la Croix Rouge',
            'coaching_requested' => false,
            'capacity' => 20,
        ]);
        $initiative1->setPublished(true);
        $initiative1->setWasPublished(true);
        $initiative1->setInterests(['sante']);
        $initiative1->incrementParticipantsCount();

        $initiative2 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_2_UUID,
            'organizer' => $this->getReference('adherent-2'),
            'committee' => $category2,
            'name' => 'Apprenez à sauver des vies',
            'category' => $category1,
            'description' => 'Venez vous initier aux gestes qui sauvent',
            'address' => PostAddress::createFrenchAddress('122 rue de Mouxy', '73100-73182', 45.7218703, 5.929463),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+9 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+ 9 days')).' 19:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
            'capacity' => 30,
        ]);
        $initiative2->setPublished(true);
        $initiative2->setWasPublished(true);
        $initiative2->incrementParticipantsCount();
        $initiative2->setInterests(['sante']);

        $initiative3 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_3_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Apprenez à sauver des vies',
            'category' => $category3,
            'description' => 'Venez vous initier aux gestes qui sauvent',
            'address' => PostAddress::createFrenchAddress('16 rue de la Paix', '75008-75108', 48.869331, 2.331595),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 09:30:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('tomorrow')).' 16:00:00'),
            'expert_assistance_needed' => true,
            'expert_assistance_description' => 'J’ai besoin d’aide pour cet événement. Besoin de quelqu’un ayant le permis poids lourd pour pouvoir
transporter tous les déchets lourds trouvés.',
            'coaching_requested' => true,
            'capacity' => 20,
        ]);
        $initiative3->setPublished(true);
        $initiative3->setWasPublished(true);
        $initiative3->incrementParticipantsCount();
        $initiative3->setInterests(['sante', 'securite']);
        $initiative3->setCoachingRequest(new CoachingRequest('Description du besoin', 'Ma solution proposée', 'Les moyens nécessaires'));

        $initiative4 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_4_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Nettoyage de la ville',
            'category' => $category1,
            'description' => 'Nous allons rendre notre ville propre',
            'address' => PostAddress::createFrenchAddress('26 rue de la Paix', '75008-75108', 48.869878, 2.332197),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 10:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 15:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
            'capacity' => 20,
        ]);
        $initiative4->setPublished(true);
        $initiative4->setWasPublished(true);
        $initiative4->incrementParticipantsCount();
        $initiative4->setInterests(['environement', 'territoire']);

        $initiative5 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_5_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Nettoyage de la Kilchberg',
            'category' => $category1,
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+11 days')).' 12:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
            'capacity' => 10,
        ]);
        $initiative5->setPublished(true);
        $initiative5->setWasPublished(true);
        $initiative5->incrementParticipantsCount();
        $initiative5->setInterests(['environement']);

        $initiative6 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_6_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Initiative citoyenne annulée',
            'category' => $category1,
            'description' => 'On a annulé cette initiative citoyenne.',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+20 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+20 days')).' 18:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
            'capacity' => 5,
        ]);
        $initiative6->setPublished(true);
        $initiative6->setWasPublished(true);
        $initiative6->cancel();
        $initiative6->incrementParticipantsCount();

        $initiative7 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_7_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Nettoyage de la ville Kilchberg',
            'category' => $category1,
            'description' => 'Nous allons rendre notre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 12:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
            'capacity' => 10,
        ]);
        $initiative7->setPublished(true);
        $initiative7->setWasPublished(true);
        $initiative7->incrementParticipantsCount(10);
        $initiative7->setInterests(['environement']);

        $initiative8 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_8_UUID,
            'organizer' => $this->getReference('adherent-13'),
            'name' => 'Nettoyage de la Kilchberg non publiée',
            'category' => $category1,
            'description' => 'Nous allons rendre Kilchberg propre',
            'address' => PostAddress::createForeignAddress('CH', '8802', 'Kilchberg', '54 Pilgerweg', 47.3164934, 8.553012),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('+15 days')).' 12:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
            'capacity' => 10,
        ]);
        $initiative8->setInterests(['environement']);

        $initiative9 = $eventFactory->createCitizenInitiativeFromArray([
            'uuid' => self::CITIZEN_INITIATIVE_9_UUID,
            'organizer' => $this->getReference('adherent-3'),
            'name' => 'Nettoyage du Vieux-Port',
            'category' => $category1,
            'description' => 'Nous allons rendre notre Vieux-Port propre',
            'address' => PostAddress::createForeignAddress('FR', '13001', 'Marseille', '25 Quai des Belges', 43.2943855, 5.3737235),
            'begin_at' => new \DateTime(date('Y-m-d', strtotime('-15 days')).' 09:00:00'),
            'finish_at' => new \DateTime(date('Y-m-d', strtotime('-15 days')).' 12:00:00'),
            'expert_assistance_needed' => false,
            'expert_assistance_description' => '',
            'coaching_requested' => false,
            'capacity' => 10,
        ]);
        $initiative9->setPublished(true);
        $initiative9->setWasPublished(true);
        $initiative9->incrementParticipantsCount(2);
        $initiative9->setInterests(['environement']);

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
        $manager->persist($initiative5);
        $manager->persist($initiative6);
        $manager->persist($initiative7);
        $manager->persist($initiative8);
        $manager->persist($initiative9);

        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative1, $author1)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative2, $author2)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative3, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative4, $author3)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative5, $author13)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative6, $author13)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative7, $author13)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative8, $author13)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative9, $author13)));
        $manager->persist($registrationFactory->createFromCommand(new EventRegistrationCommand($initiative9, $author3)));

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
