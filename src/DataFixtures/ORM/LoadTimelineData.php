<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Timeline\Measure;
use AppBundle\Entity\Timeline\MeasureTranslation;
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
            'title' => [
                'fr' => 'Élargir les horaires d’ouverture des services publics',
                'en' => 'Expand the opening hours of public services',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003'],
        ],
        'TM002' => [
            'title' => [
                'fr' => 'Créer 10 000 postes de policiers et gendarmes en plus',
                'en' => 'Create 10,000 more police positions',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002'],
        ],
        'TM003' => [
            'title' => [
                'fr' => 'Créer 12 000 postes pour les classes de CP et de CE1 dans les zones prioritaires',
                'en' => 'Create 12,000 positions for CP and CE1 classes in priority areas',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003', 'TP004'],
        ],
        'TM004' => [
            'title' => [
                'fr' => 'Réduire de 120 000 le nombre d\'emplois publics',
                'en' => 'Reduce by 120,000 the number of public jobs',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
        ],
        'TM005' => [
            'title' => [
                'fr' => 'Rendre les  ministres comptables du respect de la dépense publique',
                'en' => 'Make ministers accountable for respecting public spending',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001', 'TT002', 'TT003'],
            'profiles' => ['TP005'],
        ],
        'TM006' => [
            'title' => [
                'fr' => 'Mettre fin à l’évolution uniforme des rémunérations des fonctions publiques',
                'en' => 'Put an end to the uniform evolution of public service pay',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
        ],
        'TM007' => [
            'title' => [
                'fr' => 'Confier aux services des métropoles les compétences de leurs conseils départementaux',
                'en' => 'Entrust the departments of the cities with the skills of their departmental councils',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT001'],
            'profiles' => ['TP001'],
        ],
        'TM008' => [
            'title' => [
                'fr' => 'Basculer les cotisations salariales vers la CSG',
                'en' => 'Switch employee contributions to CSG',
            ],
            'status' => Measure::STATUS_DONE,
            'themes' => ['TT001'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
        ],
        'TM009' => [
            'title' => [
                'fr' => 'Créer une aide unique selon la taille de l’entreprise et le niveau de qualification de l’apprenti',
                'en' => 'Create a unique help depending on the size of the company',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
        ],
        'TM010' => [
            'title' => [
                'fr' => 'Créer une aide unique selon la taille de l’entreprise et le niveau de qualification de l’apprenti',
                'en' => 'Create a one-stop shop for businesses for learning and applying for help',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP001', 'TP002'],
        ],
        'TM011' => [
            'title' => [
                'fr' => 'Rassembler les deux contrats d\'alternance en un contrat unique',
                'en' => 'Bring the two work-study contracts into a single contract',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP001', 'TP002'],
        ],
        'TM012' => [
            'title' => [
                'fr' => 'Affecter la totalité de la taxe d’apprentissage au financement de l’apprentissage',
                'en' => 'Allocate the Learning Tax to Learning Funding',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT001'],
            'profiles' => ['TP001'],
        ],
        'TM013' => [
            'title' => [
                'fr' => 'Unifier la grille de rémunération des alternants',
                'en' => 'Unify the remuneration grid for alternates',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP002', 'TP003', 'TP004', 'TP005'],
        ],
        'TM014' => [
            'title' => [
                'fr' => 'Confier aux branches l\'augmentation des planchers de rémunération des alternants',
                'en' => 'To entrust to the branches the increase of the remuneration floors of the alternates',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT003'],
            'profiles' => ['TP001'],
        ],
        'TM015' => [
            'title' => [
                'fr' => 'Inscrire dans la loi les principes de la rémunération des apprentis et les montants plancher',
                'en' => 'Enclose in the law the principles of apprentice remuneration and the amounts',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002', 'TT004'],
            'profiles' => ['TP002', 'TP003'],
        ],
        'TM016' => [
            'title' => [
                'fr' => 'Définir programmes et organisation des formations avec les branches professionnelles',
                'en' => 'Define programs and organization of training with professional branches',
            ],
            'status' => Measure::STATUS_IN_PROGRESS,
            'themes' => ['TT002'],
            'profiles' => ['TP001'],
        ],
        'TM017' => [
            'title' => [
                'fr' => 'Développer un « sas » de préparation à l’alternance à la fin du collège',
                'en' => 'Develop an "airlock" for work-study preparation at the end of secondary school',
            ],
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
            $measure = new Measure(
                $metadatas['status'],
                array_map(function (string $profileReference) {
                    return $this->getReference($profileReference);
                }, $metadatas['profiles']),
                array_map(function (string $themeReference) {
                    return $this->getReference($themeReference);
                }, $metadatas['themes'])
            );

            $measure->addTranslation(new MeasureTranslation('fr', $metadatas['title']['fr']));
            $measure->addTranslation(new MeasureTranslation('en', $metadatas['title']['en']));
            $manager->persist($measure);
        }

        $manager->flush();
    }
}
