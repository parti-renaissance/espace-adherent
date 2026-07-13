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
            'bot_chatbot' => [
                'policy' => 'sliding_window',
                'limit' => 60,
                'interval' => '1 minute',
            ],
            'signup' => [
                'policy' => 'sliding_window',
                'limit' => 5,
                'interval' => '1 minute',
            ],
            'signup_code_attempt' => [
                'policy' => 'sliding_window',
                'limit' => 10,
                'interval' => '1 minute',
            ],
            'empty_hit_source_log' => [
                'policy' => 'fixed_window',
                'limit' => 5,
                'interval' => '1 hour',
            ],
            'oauth_token_error_log' => [
                'policy' => 'fixed_window',
                'limit' => 2000,
                'interval' => '1 day',
            ],
            'ses_send' => [
                'policy' => 'token_bucket',
                'limit' => '%env(int:SES_SEND_RATE_PER_SECOND)%',
                'rate' => [
                    'interval' => '1 second',
                    'amount' => '%env(int:SES_SEND_RATE_PER_SECOND)%',
                ],
                'cache_pool' => 'cache.app',
                'lock_factory' => 'lock.factory',
            ],
        ],
    ]);
};
