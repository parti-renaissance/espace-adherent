<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'messenger' => [
            'buses' => [
                'messenger.bus.default' => [
                    'middleware' => [
                        App\Messenger\RecorderMiddleware::class,
                    ],
                ],
            ],
            'transports' => [
                'sync' => 'sync://',
            ],
            'routing' => [
                App\Donation\Command\ReceivePayboxIpnResponseCommand::class => 'sync',
                App\Messenger\Message\UuidDefaultAsyncMessage::class => 'sync',
                App\Mailer\Command\AsyncSendMessageCommand::class => 'sync',
                App\Mailchimp\Webhook\Command\CatchMailchimpWebhookCallCommand::class => 'sync',
                App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand::class => 'sync',
                App\NationalEvent\Command\PaymentStatusUpdateCommand::class => 'sync',
                App\JeMengage\Hit\Command\SaveAppHitCommand::class => 'sync',
            ],
        ],
    ]);
};
