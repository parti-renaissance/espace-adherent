<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Entity\Timeline\Theme;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTimelineData extends AbstractFixture
{
    const PROFILES = [
        'TP001' => [
            'title' => 'Chef d\'entreprise',
            'slug' => 'chef-d-entreprise',
            'description' => 'Les mesures pour chefs d\'entreprise.',
        ],
        'TP002' => [
            'title' => '12-25 ans',
            'slug' => '12-25-ans',
            'description' => 'Les profils de 12 à 25ans.',
        ],
        'TP003' => [
            'title' => '25-35 ans',
            'slug' => '25-35-ans',
            'description' => 'Les profils de 25 à 35ans.',
        ],
        'TP004' => [
            'title' => '35-45 ans',
            'slug' => '35-45-ans',
            'description' => 'Les profils de 35 à 45ans.',
        ],
        'TP005' => [
            'title' => '45 ans et plus',
            'slug' => '45-ans-et-plus',
            'description' => 'Les profils de 45ans et plus.',
        ],
    ];

    const THEMES = [
        'TT001' => [
            'title' => 'Action publique et fonction publique',
            'slug' => 'action-et-fonction-publique',
            'description' => 'Action publique et fonction publique.',
            'featured' => true,
        ],
        'TT002' => [
            'title' => 'Alternance / Apprentissage',
            'slug' => 'alternance-apprentissage',
            'description' => 'Alternance / Apprentissage.',
        ],
        'TT003' => [
            'title' => 'Agriculture',
            'slug' => 'agriculture',
            'description' => 'Agriculture.',
            'featured' => true,
        ],
        'TT004' => [
            'title' => 'Culture',
            'slug' => 'culture',
            'description' => 'Culture.',
        ],
        'TT005' => [
            'title' => 'Défense',
            'slug' => 'defense',
            'description' => 'Défense.',
        ],
    ];

    const MEASURES = [
        'TM001' => [
            'title' => 'Élargir les horaires d’ouverture des services publics',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003'],
        ],
        'TM002' => [
            'title' => 'Créer 10 000 postes de policiers et gendarmes en plus',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002'],
        ],
        'TM003' => [
            'title' => 'Créer 12 000 postes pour les classes de CP et de CE1 dans les zones prioritaires',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003', 'TP004'],
        ],
        'TM004' => [
            'title' => 'Réduire de 120 000 le nombre d\'emplois publics',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
        ],
        'TM005' => [
            'title' => 'Rendre les  ministres comptables du respect de la dépense publique',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001', 'TT002', 'TT003'],
            'profiles' => ['TP005'],
        ],
        'TM006' => [
            'title' => 'Mettre fin à l’évolution uniforme des rémunérations des fonctions publiques',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
        ],
        'TM007' => [
            'title' => 'Confier aux services des métropoles les compétences de leurs conseils départementaux',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
        ],
        'TM008' => [
            'title' => 'Basculer les cotisations salariales vers la CSG',
            'status' => Measure::STATUS_DONE,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
        ],
        'TM009' => [
            'title' => 'Créer une aide unique selon la taille de l’entreprise et le niveau de qualification de l’apprenti',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
        ],
        'TM010' => [
            'title' => 'Créer un guichet unique pour les entreprises pour l\'apprentissage et la demande d\'aides',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP001', 'TP002'],
        ],
        'TM011' => [
            'title' => 'Rassembler les deux contrats d\'alternance en un contrat unique',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP001', 'TP002'],
        ],
        'TM012' => [
            'title' => 'Affecter la totalité de la taxe d’apprentissage au financement de l’apprentissage',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT001'],
            'profiles' => ['TP001'],
        ],
        'TM013' => [
            'title' => 'Unifier la grille de rémunération des alternants',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
        ],
        'TM014' => [
            'title' => 'Confier aux branches l\'augmentation des planchers de rémunération des alternants',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT003'],
            'profiles' => ['TP001'],
        ],
        'TM015' => [
            'title' => 'Inscrire dans la loi les principes de la rémunération des apprentis et les montants plancher',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT004'],
            'profiles' => ['TP002', 'TP003'],
        ],
        'TM016' => [
            'title' => 'Définir programmes et organisation des formations avec les branches professionnelles',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP001'],
        ],
        'TM017' => [
            'title' => 'Développer un « sas » de préparation à l’alternance à la fin du collège',
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT003'],
            'profiles' => ['TP001', 'TP002'],
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::PROFILES as $reference => $metadatas) {
            $profil = new Profile($metadatas['title'], $metadatas['slug'], $metadatas['description']);

            $this->addReference($reference, $profil);

            $manager->persist($profil);
        }

        foreach (self::THEMES as $reference => $metadatas) {
            $theme = new Theme(
                $metadatas['title'],
                $metadatas['slug'],
                $metadatas['description'],
                $metadatas['featured'] ?? false
            );

            $this->addReference($reference, $theme);

            $manager->persist($theme);
        }

        $manager->flush();

        foreach (self::MEASURES as $reference => $metadatas) {
            $manager->persist(new Measure(
                $metadatas['title'],
                $metadatas['status'],
                array_map(function (string $profileReference) {
                    return $this->getReference($profileReference);
                }, $metadatas['profiles']),
                array_map(function (string $themeReference) {
                    return $this->getReference($themeReference);
                }, $metadatas['themes'])
            ));
        }

        $manager->flush();
    }
}
