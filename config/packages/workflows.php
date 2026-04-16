<?php

declare(strict_types=1);

use App\VotingPlatform\Election\VoteCommandStateEnum;

return static function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'workflows' => [
            'voting_process' => [
                'type' => 'state_machine',
                'marking_store' => [
                    'type' => 'method',
                    'property' => 'state',
                ],
                'initial_marking' => VoteCommandStateEnum::INITIALIZE,
                'supports' => [
                    App\VotingPlatform\Election\VoteCommand\VoteCommand::class,
                ],
                'places' => [
                    VoteCommandStateEnum::INITIALIZE,
                    VoteCommandStateEnum::VOTE,
                    VoteCommandStateEnum::CONFIRM,
                    VoteCommandStateEnum::FINISH,
                ],
                'transitions' => [
                    VoteCommandStateEnum::TO_VOTE => [
                        'from' => [
                            VoteCommandStateEnum::INITIALIZE,
                            VoteCommandStateEnum::CONFIRM,
                        ],
                        'to' => VoteCommandStateEnum::VOTE,
                    ],
                    VoteCommandStateEnum::TO_CONFIRM => [
                        'from' => 'vote',
                        'to' => VoteCommandStateEnum::CONFIRM,
                    ],
                    VoteCommandStateEnum::TO_FINISH => [
                        'from' => 'confirm',
                        'to' => VoteCommandStateEnum::FINISH,
                    ],
                ],
            ],
        ],
    ]);
};
