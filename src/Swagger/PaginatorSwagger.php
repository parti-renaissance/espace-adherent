<?php

namespace App\Swagger;

class PaginatorSwagger
{
    public const DEFINITION = [
        'type' => 'object',
        'properties' => [
            'total_items' => [
                'type' => 'integer',
            ],
            'items_per_page' => [
                'type' => 'integer',
            ],
            'count' => [
                'type' => 'integer',
            ],
            'current_page' => [
                'type' => 'integer',
            ],
            'last_page' => [
                'type' => 'integer',
            ],
        ],
    ];

    public static function getPaginatedResponseFor(string $definition): array
    {
        $model = substr($definition, 0, strpos($definition, '-'));

        return [
            'description' => "Paginated $model collection response",
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'metadatas' => self::DEFINITION,
                    'items' => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => "#/definitions/$definition",
                        ],
                    ],
                ],
            ],
        ];
    }
}
