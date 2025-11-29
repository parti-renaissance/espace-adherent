<?php

declare(strict_types=1);

namespace App\Redirection\Dynamic;

class RedirectionsProvider
{
    public const TO_PATH = 'to_path';
    public const TO_ROUTE = 'to_route';
    public const TO_REMOVE_UUID = 'to_remove_uuid';

    public const REDIRECTIONS = [
        self::TO_PATH => [
            '/articles/tribunes/' => '/articles/opinions/',
        ],
        self::TO_ROUTE => [
            '/article/' => 'article_view',
            '/amp/article/' => 'article_view',
            '/amp/proposition/' => 'program_proposal',
            '/amp/transformer-la-france/' => 'app_explainer_article_show',
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
