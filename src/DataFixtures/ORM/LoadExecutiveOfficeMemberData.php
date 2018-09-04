<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Biography\ExecutiveOfficeMember;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadExecutiveOfficeMemberData extends AbstractFixture implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $person1 = new ExecutiveOfficeMember(
            null,
            'Christophe',
            'Castaner',
            '',
            '',
            true,
            'Délégué général du mouvement',
            true
        );

        $person1->setFacebookProfile('https://www.facebook.com/christophe.castaner');
        $person1->setTwitterProfile('https://twitter.com/ccastaner');
        $person1->setInstagramProfile('https://www.instagram.com/christophe_castaner');

        $person2 = new ExecutiveOfficeMember(
            null,
            'Caroline',
            'Abadie',
            'Députée LaREM de l’Isère, chargée d’animer la réflexion sur les libertés publiques, la justice, la défense et la sécurité intérieure.',
            'Députée LaREM de l’Isère, chargée d’animer la réflexion sur les libertés publiques, la justice, la défense et la sécurité intérieure, ainsi que sur les nouveaux risques. Déléguée adjointe : Marie-Hélène REGNIER, référente LaREM dans l’Aude.',
            true,
            'Déléguée à la Garantie des libertés et de la sécurité',
            false
        );

        $person2->setFacebookProfile('https://facebook.com/EnMarche');
        $person2->setTwitterProfile('https://twitter.com/enmarchefr');
        $person2->setInstagramProfile('https://www.instagram.com/enmarchefr');
        $person2->setLinkedInProfile('https://fr.linkedin.com/company/en-marche');

        $person3 = new ExecutiveOfficeMember(
            null,
            'Béatrice',
            'Agamennone',
            '47 ans, mariée, 3 enfants, référente LaREM en Moselle depuis octobre 2016. Elle est ingénieur en chef des travaux publics de l’Etat, directrice adjointe du Centre d’études et d’expertises sur l’Environnement, les Mobilités et l’Aménagement, Est.',
            '47 ans, mariée, 3 enfants, référente LaREM en Moselle depuis octobre 2016. Elle est ingénieur en chef des travaux publics de l’Etat, directrice adjointe du Centre d’études et d’expertises sur l’Environnement, les Mobilités et l’Aménagement, Est. Elle est adjointe au Maire de Metz, vice-présidente de l’agence d’urbanisme d’Agglomérations de Moselle et conseillère communautaire de Metz Métropole.',
            true,
            'Référente',
            false
        );

        $person3->setFacebookProfile('https://facebook.com/EnMarche');
        $person3->setTwitterProfile('https://twitter.com/enmarchefr');
        $person3->setInstagramProfile('https://www.instagram.com/enmarchefr');
        $person3->setLinkedInProfile('https://fr.linkedin.com/company/en-marche');

        $person4 = new ExecutiveOfficeMember(
            null,
            'Laëtitia',
            'Avia',
            '32 ans, députée de Paris. Engagée de la 1ère heure auprès d\'Emmanuel Macron, Laetitia a été experte du programme Justice puis référente territoriale d’En Marche.',
            '32 ans, députée de Paris. Engagée de la 1ère heure auprès d\'Emmanuel Macron, Laetitia a été experte du programme Justice puis référente territoriale d’En Marche. Issue des quartiers populaires, engagée pour l\'égalité des chances, elle est avocate au Barreau de Paris où elle a fondé son cabinet.',
            true,
            'Déléguée à la Communication',
            false
        );

        $person4->setFacebookProfile('https://facebook.com/EnMarche');
        $person4->setTwitterProfile('https://twitter.com/enmarchefr');
        $person4->setInstagramProfile('https://www.instagram.com/enmarchefr');
        $person4->setLinkedInProfile('https://fr.linkedin.com/company/en-marche');

        $manager->persist($person1);
        $manager->persist($person2);
        $manager->persist($person3);
        $manager->persist($person4);

        $manager->flush();
    }
}
