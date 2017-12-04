<?php

namespace AppBundle\Migration;

use AppBundle\Entity\BaseEventCategory;
use AppBundle\Entity\CitizenProjectCategory;
use AppBundle\Entity\CitizenProjectCategorySkill;
use AppBundle\Entity\CitizenProjectSkill;
use Doctrine\ORM\EntityManagerInterface;

class CitizenProjectCategorySkillMigration
{
    private $entityManager;

    const CATEGORIES_SKILLS_MAPPING = [
        'C001' => [
            ['skill' => 'S001', 'promotion' => true],
            ['skill' => 'S002', 'promotion' => true],
            ['skill' => 'S003', 'promotion' => true],
            ['skill' => 'S004', 'promotion' => false],
            ['skill' => 'S005', 'promotion' => false],
            ['skill' => 'S006', 'promotion' => false],
        ],
        'C002' => [
            ['skill' => 'S007', 'promotion' => true],
            ['skill' => 'S008', 'promotion' => true],
            ['skill' => 'S009', 'promotion' => true],
            ['skill' => 'S010', 'promotion' => false],
            ['skill' => 'S011', 'promotion' => false],
            ['skill' => 'S012', 'promotion' => false],
        ],
        'C003' => [
            ['skill' => 'S013', 'promotion' => true],
            ['skill' => 'S014', 'promotion' => true],
            ['skill' => 'S015', 'promotion' => true],
            ['skill' => 'S016', 'promotion' => false],
        ],
        'C004' => [
            ['skill' => 'S017', 'promotion' => true],
            ['skill' => 'S018', 'promotion' => true],
            ['skill' => 'S019', 'promotion' => true],
            ['skill' => 'S020', 'promotion' => false],
        ],
        'C005' => [
            ['skill' => 'S021', 'promotion' => true],
            ['skill' => 'S022', 'promotion' => true],
            ['skill' => 'S023', 'promotion' => true],
            ['skill' => 'S024', 'promotion' => false],
        ],
    ];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function populate()
    {
        $manager = $this->entityManager;
        $this->loadCategoriesSkills();
        $manager->flush();
    }

    private function loadCategoriesSkills(): void
    {
        foreach (self::CATEGORIES_SKILLS_MAPPING as $category => $items) {
            // Adds categories
            $citizenProjectCategory = new CitizenProjectCategory(
                self::categoryProvider()[$category], BaseEventCategory::ENABLED
            );
            $this->entityManager->persist($citizenProjectCategory);

            // Adds skills
            foreach ($items as $item) {
                $citizenProjectSkill = new CitizenProjectSkill(self::skillProvider()[$item['skill']]);
                $this->entityManager->persist($citizenProjectSkill);

                // Adds the categories / skills relation
                $citizenProjectCategorySkill = new CitizenProjectCategorySkill(
                    $citizenProjectCategory, $citizenProjectSkill, $item['promotion']
                );

                $this->entityManager->persist($citizenProjectCategorySkill);
            }
        }
    }

    private static function categoryProvider(): array
    {
        return [
            'C001' => 'Nature et Environnement',
            'C002' => 'Education, culture et citoyenneté',
            'C003' => 'Culture',
            'C004' => 'Lien social et aide aux personnes en difficulté',
            'C005' => 'Santé',
        ];
    }

    private static function skillProvider(): array
    {
        return [
            'S001' => 'Paysage',
            'S002' => 'Jardinage / Botanique',
            'S003' => 'Gestion des parcs nationaux',
            'S004' => 'Isolation thermique et acoustique',
            'S005' => 'Horticulture',
            'S006' => 'Gestion des déchets',
            'S007' => 'Professeurs du primaire',
            'S008' => 'Professeurs du secondaire',
            'S009' => 'Professeurs d’université',
            'S010' => 'Chercheurs',
            'S011' => 'Éducateurs et animateurs spécialisés',
            'S012' => 'Parents d’élèves',
            'S013' => 'Artistes',
            'S014' => 'Professionnels de la culture',
            'S015' => 'Architecte',
            'S016' => 'Peintres et sculpteurs',
            'S017' => 'Solidarité intergénérationnel ',
            'S018' => 'Lutte contre l’exclusion',
            'S019' => 'Egalité des chances',
            'S020' => 'Conversations',
            'S021' => 'Médecin',
            'S022' => 'Infirmier-e',
            'S023' => 'Aide-soignant',
            'S024' => 'Psychologue',
            'S025' => 'Secouriste',
        ];
    }
}
