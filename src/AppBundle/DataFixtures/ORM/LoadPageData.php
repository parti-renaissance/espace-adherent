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
        $description = 'Pour ceux qui sont convaincus que le pays est bloqué, qui ont le goût du travail, du progrès, '.
            'du risque, qui vivent pour la liberté, l\'égalité, et l\'Europe.';

        $manager->persist($factory->createFromArray([
            'title' => 'Emmanuel Macron - Ce que je suis',
            'slug' => 'emmanuel-macron-ce-que-je-suis',
            'description' => $description,
            'content' => trim(self::$ceQueJeSuis),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Emmanuel Macron - Révolution',
            'slug' => 'emmanuel-macron-revolution',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Le mouvement - Nos valeurs',
            'slug' => 'le-mouvement-nos-valeurs',
            'description' => $description,
            'content' => trim(self::$nosValeurs),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Le mouvement - Notre organisation',
            'slug' => 'le-mouvement-notre-organisation',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../organization.html'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Le mouvement - Les comités',
            'slug' => 'le-mouvement-les-comites',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Le mouvement - Les événements',
            'slug' => 'le-mouvement-les-evenements',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Le mouvement - Devenez bénévole',
            'slug' => 'le-mouvement-devenez-benevole',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Mentions légales',
            'slug' => 'mentions-legales',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../legalities.md'),
        ]));

        $manager->persist($factory->createFromArray([
            'title' => 'Les propositions d\'Emmanuel Macron',
            'slug' => 'emmanuel-macron-propositions',
            'description' => 'Le moment que nous vivons est celui d’une refondation profonde de la France. '.
                'Voici celle que nous vous proposons.',
            'content' => self::$propositions,
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

    private static $propositions = '
<p class="text--body">
    Le moment que nous vivons est celui d’une refondation profonde de la France. Voici celle que nous vous proposons.
    Le rôle de la politique est de déployer un cadre qui permettra à chacune et à chacun de trouver sa voie, de
    devenir maître de son destin, d’exercer sa liberté, de pouvoir choisir sa vie, puis de pouvoir vivre de son
    travail. C’est avec cette promesse d’émancipation que la politique doit renouer. Le chômage n’est pas une
    fatalité, mais un combat que nous pouvons gagner grâce aux mesures juste qui donneront à tous les acteurs la
    capacité de faire face aux changements.
</p>
<p class="text--body text--bold text--medium-small">
    <a href="https://staging.en-marche.fr/article/comment-emmanuel-macron-a-t-il-construit-ses-propositions">
        Comment Emmanuel Macron a-t-il construit ses propositions ?
    </a>
</p>
    ';
}
