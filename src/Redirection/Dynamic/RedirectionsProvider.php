<?php

namespace App\Redirection\Dynamic;

class RedirectionsProvider
{
    const TO_PATH = 'to_path';
    const TO_ROUTE = 'to_route';
    const TO_REMOVE_UUID = 'to_remove_uuid';

    const REDIRECTIONS = [
        self::TO_PATH => [
            '/articles/tribunes/' => '/articles/opinions/',
        ],
        self::TO_ROUTE => [
            '/article/' => 'article_view',
            '/amp/article/' => 'amp_article_view',
            '/amp/proposition/' => 'amp_proposal_view',
            '/amp/transformer-la-france/' => 'amp_explainer_article_show',
        ],
        self::TO_REMOVE_UUID => [
            '/evenements/' => '/evenements',
            '/initiative-citoyenne/' => '/initiative-citoyenne',
            '/comites/' => '/comites',
        ],
    ];

    public function get(string $type): array
    {
        if (!\array_key_exists($type, self::REDIRECTIONS)) {
            return [];
        }

        return self::REDIRECTIONS[$type];
    }
}
