<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Entity\Donation;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Envoie des notifications Slack pour les événements importants.
 */
class SlackNotifier
{
    private const WEBHOOK_URL = 'https://hooks.slack.com/services/XXXXXXXXX/YYYYYYY/ZZZZZZZZ';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $slackWebhookUrl = self::WEBHOOK_URL,
    ) {
    }

    public function notifyHighValueDonation(Donation $donation): void
    {
        $amount = number_format($donation->getAmountInEuros(), 2, ',', ' ');
        $donator = $donation->getDonator();

        $message = [
            'text' => "🎉 *Don important reçu !*",
            'attachments' => [
                [
                    'color' => '#36a64f',
                    'fields' => [
                        [
                            'title' => 'Montant',
                            'value' => "{$amount} €",
                            'short' => true,
                        ],
                        [
                            'title' => 'Donateur',
                            'value' => $donator?->getFullName() ?? 'Anonyme',
                            'short' => true,
                        ],
                        [
                            'title' => 'Email',
                            'value' => $donator?->getEmailAddress() ?? 'N/A',
                            'short' => true,
                        ],
                        [
                            'title' => 'Type',
                            'value' => $donation->hasSubscription() ? 'Récurrent' : 'Ponctuel',
                            'short' => true,
                        ],
                    ],
                ],
            ],
        ];

        // Appel synchrone bloquant
        $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => $message,
            'timeout' => 5,
        ]);
    }

    public function notifyError(string $context, array $details = []): void
    {
        $message = [
            'text' => "⚠️ *Erreur IPN Donation*\n```{$context}```",
            'attachments' => [
                [
                    'color' => '#ff0000',
                    'fields' => array_map(
                        fn ($key, $value) => ['title' => $key, 'value' => json_encode($value), 'short' => true],
                        array_keys($details),
                        array_values($details)
                    ),
                ],
            ],
        ];

        $this->httpClient->request('POST', $this->slackWebhookUrl, [
            'json' => $message,
            'timeout' => 5,
        ]);
    }
}
