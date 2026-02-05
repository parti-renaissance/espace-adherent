<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('algolia_search', [
        'doctrineSubscribedEvents' => [
        ],
        'prefix' => 'app_%env(ALGOLIA_INDEX_PREFIX)%_',
        'indices' => [
            [
                'name' => 'custom_search_result',
                'class' => App\Entity\CustomSearchResult::class,
            ],
            [
                'name' => 'proposal',
                'class' => App\Entity\Proposal::class,
                'index_if' => 'isIndexable',
            ],
            [
                'name' => 'jemengage_timeline_feed',
                'class' => App\Entity\Algolia\AlgoliaJeMengageTimelineFeed::class,
                'index_if' => 'isIndexable',
            ],
        ],
    ]);
};
