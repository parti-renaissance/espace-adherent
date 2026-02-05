<?php

declare(strict_types=1);

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('env(RABBITMQ_CONNECT_TIMEOUT)', '10');

    $parameters->set('env(RABBITMQ_WAIT_TIMEOUT)', '30');

    $parameters->set('env(RABBITMQ_READ_TIMEOUT)', '60');

    $parameters->set('env(RABBITMQ_WRITE_TIMEOUT)', '60');

    $parameters->set('env(RABBITMQ_HEARTBEAT)', '120');

    $parameters->set('env(RABBITMQ_DSN)', 'phpamqplib://%env(RABBITMQ_USER)%:%env(RABBITMQ_PASSWORD)%@%env(RABBITMQ_HOST)%:%env(RABBITMQ_PORT)%/%%2f?connect_timeout=%env(RABBITMQ_CONNECT_TIMEOUT)%&wait_timeout=%env(RABBITMQ_WAIT_TIMEOUT)%&read_timeout=%env(RABBITMQ_READ_TIMEOUT)%&write_timeout=%env(RABBITMQ_WRITE_TIMEOUT)%&heartbeat=%env(RABBITMQ_HEARTBEAT)%&keepalive=1');

    $containerConfigurator->extension('framework', [
        'messenger' => [
            'buses' => [
                'messenger.bus.default' => [
                    'middleware' => [
                        'doctrine_ping_connection',
                        App\Messenger\LockMiddleware::class,
                        'doctrine_close_connection',
                    ],
                ],
            ],
            'serializer' => [
                'default_serializer' => 'messenger.transport.symfony_serializer',
                'symfony_serializer' => [
                    'format' => 'json',
                    'context' => [
                    ],
                ],
            ],
            'failure_transport' => 'failed',
            'transports' => [
                'failed' => 'doctrine://default?queue_name=failed',
                'default' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 10000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'async.command',
                        ],
                        'queues' => [
                            'default' => [
                                'binding_keys' => [
                                    'async.command',
                                ],
                            ],
                        ],
                    ],
                ],
                'pap' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 10000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'async.pap',
                        ],
                        'queues' => [
                            'pap' => [
                                'binding_keys' => [
                                    'async.pap',
                                ],
                            ],
                        ],
                    ],
                ],
                'mailchimp_sync' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 2000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'mailchimp.sync',
                        ],
                        'queues' => [
                            'mailchimp_sync' => [
                                'binding_keys' => [
                                    'mailchimp.sync',
                                ],
                            ],
                        ],
                    ],
                ],
                'mailchimp_batch' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 10000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'mailchimp.batch',
                        ],
                        'queues' => [
                            'mailchimp_batch' => [
                                'binding_keys' => [
                                    'mailchimp.batch',
                                ],
                                'arguments' => [
                                    'x-max-priority' => 100,
                                ],
                            ],
                        ],
                    ],
                ],
                'mailchimp_campaign' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 2000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'mailchimp.campaign',
                        ],
                        'queues' => [
                            'mailchimp_campaign' => [
                                'binding_keys' => [
                                    'mailchimp.campaign',
                                ],
                            ],
                        ],
                    ],
                ],
                'notification' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 10000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'async.notification',
                        ],
                        'queues' => [
                            'notification' => [
                                'binding_keys' => [
                                    'async.notification',
                                ],
                            ],
                        ],
                    ],
                ],
                'chatbot' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'max_retries' => 20,
                        'delay' => 3000,
                        'multiplier' => 1,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'async.chatbot',
                        ],
                        'queues' => [
                            'chatbot' => [
                                'binding_keys' => [
                                    'async.chatbot',
                                ],
                            ],
                        ],
                    ],
                ],
                'event' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 10000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'async.event',
                        ],
                        'queues' => [
                            'event' => [
                                'binding_keys' => [
                                    'async.event',
                                ],
                            ],
                        ],
                    ],
                ],
                'cronjob' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 10000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'async.cronjob',
                        ],
                        'queues' => [
                            'cronjob' => [
                                'binding_keys' => [
                                    'async.cronjob',
                                ],
                            ],
                        ],
                    ],
                ],
                'sequential' => [
                    'dsn' => '%env(RABBITMQ_DSN)%',
                    'retry_strategy' => [
                        'delay' => 10000,
                    ],
                    'options' => [
                        'exchange' => [
                            'name' => 'messenger-topic',
                            'type' => 'topic',
                            'default_publish_routing_key' => 'async.sequential',
                        ],
                        'queues' => [
                            'sequential' => [
                                'binding_keys' => [
                                    'async.sequential',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'routing' => [
                App\Chatbot\Command\RefreshThreadCommand::class => 'chatbot',
                App\Event\Command\EventNotificationCommandInterface::class => 'event',
                App\Mailchimp\CampaignMessageInterface::class => 'mailchimp_campaign',
                App\Mailchimp\SynchronizeMessageInterface::class => 'mailchimp_sync',
                App\Mailer\Command\AsyncSendMessageCommand::class => 'notification',
                App\Messenger\Message\AsynchronousMessageInterface::class => 'default',
                App\Messenger\Message\CronjobMessageInterface::class => 'cronjob',
                App\Messenger\Message\SequentialMessageInterface::class => 'sequential',
                App\Notifier\AsyncNotificationInterface::class => 'notification',
                App\Pap\Command\AsynchronousMessageInterface::class => 'pap',
            ],
        ],
    ]);
};
