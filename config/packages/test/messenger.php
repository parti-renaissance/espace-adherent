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
                'test_async' => 'in-memory://',
            ],
            'routing' => [
                App\Sentry\Webhook\Command\SentryWebhookCommand::class => 'test_async',
                App\Donation\Command\ReceivePayboxIpnResponseCommand::class => 'sync',
                App\Messenger\Message\UuidDefaultAsyncMessage::class => 'sync',
                App\JeMengage\Push\Command\SendPushChunkCommand::class => 'sync',
                App\Mailer\Command\AsyncSendMessageCommand::class => 'sync',
                App\Mailchimp\Webhook\Command\CatchMailchimpWebhookCallCommand::class => 'sync',
                App\SocialNetwork\Webhook\Command\SocialNetworkFeedWebhookCommand::class => 'sync',
                App\Video\Transcoding\VideoTranscodingMessageInterface::class => 'sync',
                App\SocialNetwork\Image\FeedImagePublishingMessageInterface::class => 'sync',
                App\Adherent\Tag\Command\AsyncRefreshAdherentTagCommand::class => 'sync',
                App\NationalEvent\Command\PaymentStatusUpdateCommand::class => 'sync',
                App\JeMengage\Hit\Command\SaveAppHitCommand::class => 'sync',
                App\Mailchimp\Campaign\Audience\Message\MailchimpAudienceMessageInterface::class => 'sync',
                App\Ses\Campaign\Message\SesCampaignMessageInterface::class => 'sync',
                App\Ses\Webhook\SesWebhookMessageInterface::class => 'sync',
            ],
        ],
    ]);
};
