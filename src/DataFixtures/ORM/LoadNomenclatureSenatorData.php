<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Nomenclature\Senator;
use AppBundle\Entity\Nomenclature\SenatorArea;
use AppBundle\ValueObject\Genders;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadNomenclatureSenatorData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $areas = [];
        foreach ($this->getSenatorAreas() as $areaData) {
            $senatorArea = new SenatorArea(...$areaData);

            $manager->persist($senatorArea);

            $areas[$senatorArea->getCode()] = $senatorArea;
        }

        $manager->persist($this->createSenator(
            $areas['01'],
            'Alban',
            'Martin',
            Genders::MALE,
            'alban.martin@en-marche-dev.fr',
            'alban-martin',
            'https://albanmartin.fr',
            'https://www.facebook.com/albanmartin-fake',
            'https://twitter.com/albanmartin-fake'
        ));
        $manager->persist($this->createSenator(
            $areas['02'],
            'John',
            'Doe',
            Genders::MALE,
            'john.doe@en-marche-dev.fr',
            'john-doe',
            'https://johndoe.fr',
            'https://wwww.facebook.com/johndoe-fake',
            'https://twitter.com/johndoe-fake',
        ));
        $manager->persist($this->createSenator(
            $areas['FOF'],
            'Jack',
            'Doe',
            Genders::MALE,
            'jack.doe@en-marche-dev.fr',
            'jack-doe',
            'https://jackdoe.fr',
            'https://wwww.facebook.com/jackdoe-fake',
            'https://twitter.com/jackdoe-fake',
            ));

        $manager->flush();
    }

    private function createSenator(
        SenatorArea $area,
        string $firstName,
        string $lastName,
        string $gender,
        string $emailAddress,
        string $slug = null,
        ?string $websiteUrl = null,
        ?string $facebookUrl = null,
        ?string $twitterUrl = null,
        string $status = Senator::ENABLED
    ): Senator {
        $directory = __DIR__.'/../../DataFixtures/legislatives';
        $description = file_get_contents(sprintf('%s/description.txt', $directory));

        return new Senator(
            $area,
            $firstName,
            $lastName,
            $gender,
            $emailAddress,
            $slug,
            $description,
            $websiteUrl,
            $facebookUrl,
            $twitterUrl,
            $status
        );
    }

    /**
     * @return SenatorArea[]
     */
    private function getSenatorAreas(): array
    {
        return [
            // France Métropolitaine
            ['01', 'Ain', ['01']],
            ['02', 'Aisne', ['02']],
            ['19', 'Corrèze', ['19']],
            ['2A', 'Corse Sud', ['20', '2A', '2B', 'Corse']],
            ['2B', 'Haute Corse', ['20', '2A', '2B', 'Corse']],
            ['21', "Côte d'Or", ['21']],
            ['73', 'Savoie', ['73']],
            ['74', 'Haute-Savoie', ['74', 'Haute Savoie']],
            ['75', 'Paris', ['75']],
            ['92', 'Hauts-de-Seine', ['92', 'Hauts de Seine']],

            // Outre-Mer
            ['971', 'Guadeloupe', ['971']],
            ['972', 'Martinique', ['972']],
            ['973', 'Guyane', ['973']],
            ['974', 'La Réunion', ['974']],
            ['975', 'Saint-Pierre-et-Miquelon', ['975', 'Saint Pierre et Miquelon']],
            ['976', 'Mayotte', ['976']],
            ['977', 'Saint-Barthélemy', ['977', 'Saint Barthelemy']],
            ['978', 'Saint-Martin', ['978', 'Saint Martin']],
            ['986', 'Wallis-et-Futuna', ['986', 'Wallis et Futuna']],
            ['987', 'Polynésie française', ['987']],
            ['988', 'Nouvelle-Calédonie', ['988', 'Nouvelle Calédonie']],
            ['989', 'Clipperton', ['989']],

            // Français de l'étranger
            ['FOF', 'Français de l\'étranger', ['Français de l\'étranger']],
        ];
    }
}
