<?php

declare(strict_types=1);

use App\Adherent\Contribution\ContributionRequestStateEnum;
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
            'contribution_process' => [
                'type' => 'state_machine',
                'marking_store' => [
                    'type' => 'method',
                    'property' => 'state',
                ],
                'initial_marking' => ContributionRequestStateEnum::STATE_START,
                'supports' => [
                    App\Adherent\Contribution\ContributionRequest::class,
                ],
                'places' => [
                    ContributionRequestStateEnum::STATE_START,
                    ContributionRequestStateEnum::STATE_NO_CONTRIBUTION_NEEDED,
                    ContributionRequestStateEnum::STATE_CONTRIBUTION_ALREADY_DONE,
                    ContributionRequestStateEnum::STATE_FILL_REVENUE,
                    ContributionRequestStateEnum::STATE_SEE_CONTRIBUTION_AMOUNT,
                    ContributionRequestStateEnum::STATE_FILL_CONTRIBUTION_INFORMATIONS,
                    ContributionRequestStateEnum::STATE_CONTRIBUTION_COMPLETE,
                ],
                'transitions' => [
                    ContributionRequestStateEnum::TO_FILL_REVENUE => [
                        'from' => [
                            ContributionRequestStateEnum::STATE_START,
                            ContributionRequestStateEnum::STATE_FILL_REVENUE,
                            ContributionRequestStateEnum::STATE_NO_CONTRIBUTION_NEEDED,
                            ContributionRequestStateEnum::STATE_SEE_CONTRIBUTION_AMOUNT,
                            ContributionRequestStateEnum::STATE_FILL_CONTRIBUTION_INFORMATIONS,
                        ],
                        'to' => ContributionRequestStateEnum::STATE_FILL_REVENUE,
                    ],
                    ContributionRequestStateEnum::TO_NO_CONTRIBUTION_NEEDED => [
                        'from' => [
                            ContributionRequestStateEnum::STATE_FILL_REVENUE,
                            ContributionRequestStateEnum::STATE_NO_CONTRIBUTION_NEEDED,
                        ],
                        'to' => ContributionRequestStateEnum::STATE_NO_CONTRIBUTION_NEEDED,
                    ],
                    ContributionRequestStateEnum::TO_CONTRIBUTION_ALREADY_DONE => [
                        'from' => [
                            ContributionRequestStateEnum::STATE_FILL_REVENUE,
                            ContributionRequestStateEnum::STATE_CONTRIBUTION_ALREADY_DONE,
                        ],
                        'to' => ContributionRequestStateEnum::STATE_CONTRIBUTION_ALREADY_DONE,
                    ],
                    ContributionRequestStateEnum::TO_SEE_CONTRIBUTION_AMOUNT => [
                        'from' => [
                            ContributionRequestStateEnum::STATE_SEE_CONTRIBUTION_AMOUNT,
                            ContributionRequestStateEnum::STATE_FILL_REVENUE,
                            ContributionRequestStateEnum::STATE_FILL_CONTRIBUTION_INFORMATIONS,
                        ],
                        'to' => ContributionRequestStateEnum::STATE_SEE_CONTRIBUTION_AMOUNT,
                    ],
                    ContributionRequestStateEnum::TO_FILL_CONTRIBUTION_INFORMATIONS => [
                        'from' => [
                            ContributionRequestStateEnum::STATE_FILL_CONTRIBUTION_INFORMATIONS,
                            ContributionRequestStateEnum::STATE_SEE_CONTRIBUTION_AMOUNT,
                        ],
                        'to' => ContributionRequestStateEnum::STATE_FILL_CONTRIBUTION_INFORMATIONS,
                    ],
                    ContributionRequestStateEnum::TO_CONTRIBUTION_COMPLETE => [
                        'from' => [
                            ContributionRequestStateEnum::STATE_CONTRIBUTION_COMPLETE,
                            ContributionRequestStateEnum::STATE_FILL_CONTRIBUTION_INFORMATIONS,
                        ],
                        'to' => ContributionRequestStateEnum::STATE_CONTRIBUTION_COMPLETE,
                    ],
                ],
            ],
        ],
    ]);
};
