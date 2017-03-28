<?php

namespace AppBundle\Search\Algolia;

/**
 * This class is used to find the route of a specific slug.
 * PageController has customs routes that will fetch a Page using an harcorded slug.
 */
class PageSlugToUrl
{
    const URLS = [
        'emmanuel-macron-ce-que-je-suis' => '/emmanuel-macron',
        'emmanuel-macron-revolution' => '/emmanuel-macron/revolution',
        'le-mouvement-nos-valeurs' => '/le-mouvement',
        'le-mouvement-notre-organisation' => '/le-mouvement/notre-organisation',
        'le-mouvement-les-comites' => '/le-mouvement/les-comites',
        'le-mouvement-les-evenements' => '/le-mouvement/les-evenements',
        'le-mouvement-devenez-benevole' => '/le-mouvement/devenez-benevole',
        'mentions-legales' => '/mentions-legales',
        'emmanuel-macron-propositions' => '/emmanuel-macron/le-programme',
        'le-mouvement-legislatives' => '/le-mouvement/legislatives',
        'desintox' => '/emmanuel-macron/desintox',
    ];

    public static function getUrl(string $slug): ?string
    {
        return self::URLS[$slug] ?? null;
    }
}
