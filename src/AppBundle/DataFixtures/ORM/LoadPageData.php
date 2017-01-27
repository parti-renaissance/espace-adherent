<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadPageData implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('app.content.page_factory');

        $manager->persist($factory->createFromArray([
            'title' => 'Le mouvement - Nos valeurs',
            'slug' => 'le-mouvement-nos-valeurs',
            'description' => 'Nos valeurs',
            'content' => trim(self::$nosValeurs),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Emmanuel Macron - Ce que je suis',
            'slug' => 'emmanuel-macron-ce-que-je-suis',
            'description' => 'Ce que je suis',
            'content' => trim(self::$ceQueJeSuis),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Emmanuel Macron - Révolution',
            'slug' => 'emmanuel-macron-revolution',
            'description' => 'Révolution',
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        $manager->flush();
    }

    private static $nosValeurs = '
        Nous croyons au progrès face à tous les conservatismes. Nous croyons que le temps n’est pas aux petits
        ajustements mais à l’innovation radicale. Nous croyons en l’émancipation de tous. Nous croyons que le 
        destin de l’Europe et celui de la France sont indissociables. Nous croyons en notre capacité à agir
        ensemble.
    ';

    private static $ceQueJeSuis = '
        Il y a, me concernant, des choses que beaucoup savent déjà. J’ai fait l’ENA,
        j’ai travaillé dans une banque d’affaires, puis pour François Hollande durant la campagne présidentielle de
        2012, et j’ai été à son service durant plus de deux années comme Secrétaire général adjoint de l’Elysée. J’ai
        été Ministre de l’Economie, de l’Industrie et du Numérique, avec passion, jusqu’à la fin du mois d’août 2016.
        En décembre 2016, j’ai démissionné de la fonction publique. Voilà pour la biographie officielle.
    ';
}
