<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'rate_limiter' => [
            'activation_account_retry' => [
                'policy' => 'sliding_window',
                'limit' => 3,
                'interval' => '1 minute',
            ],
            'payment_retry' => [
                'policy' => 'sliding_window',
                'limit' => 5,
                'interval' => '30 minute',
            ],
            'change_email' => [
                'policy' => 'sliding_window',
                'limit' => 3,
                'interval' => '5 minutes',
            ],
            'ohme_api_request' => [
                'policy' => 'fixed_window',
                'limit' => 90,
                'interval' => '1 minute',
            ],
        ],
    ]);
};
