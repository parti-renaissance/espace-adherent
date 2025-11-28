<?php

declare(strict_types=1);

namespace App\Donation\Request;

use App\Entity\Transaction;
use App\Exception\InvalidDonationCallbackException;
use Cocur\Slugify\SlugifyInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DonationRequestUtils
{
    public const RESULT_STATUS_EFFECTUE = 'effectue';
    public const RESULT_STATUS_ERREUR = 'erreur';

    private const CALLBACK_TOKEN = 'donation_callback_token';
    private const STATUS_TOKEN = 'donation_status_token';
    private const RETRY_TOKEN = 'donation_retry_token';
    private const PAYBOX_SUCCESS = 'donation_paybox_success';
    private const PAYBOX_UNKNOWN = 'donation_paybox_unknown';
    private const PAYBOX_STATUSES = [
        // Success
        Transaction::PAYBOX_SUCCESS => self::PAYBOX_SUCCESS,

        // Platform or authorization center error
        Transaction::PAYBOX_CONNECTION_FAILED => 'paybox',
        Transaction::PAYBOX_INTERNAL_ERROR => 'paybox',

        // Invalid card number/validity
        Transaction::PAYBOX_CARD_NUMBER_INVALID => 'invalid-card',
        Transaction::PAYBOX_CARD_END_DATE_INVALID => 'invalid-card',
        Transaction::PAYBOX_CARD_UNAUTHORIZED => 'invalid-card',

        // Timeout
        Transaction::PAYBOX_PAYMENT_PAGE_TIMEOUT => 'timeout',

        // Other
        self::PAYBOX_UNKNOWN => 'error',
    ];

    public function __construct(
        private readonly CsrfTokenManagerInterface $tokenManager,
        private readonly SlugifyInterface $slugify,
    ) {
    }

    public function buildCallbackParameters(): array
    {
        return ['_callback_token' => $this->tokenManager->getToken(self::CALLBACK_TOKEN)];
    }

    public function extractPayboxResultFromCallback(Request $request, string $token): array
    {
        $this->validateCallback($token);

        $data = array_merge($request->query->all(), [
            'authorization' => $request->query->get('authorization'),
            'result' => $request->query->get('result', self::PAYBOX_UNKNOWN),
        ]);

        unset($data['id'], $data['Sign']);

        return $data;
    }

    public function createCallbackStatus(string $resultCode, string $donationUuid): array
    {
        $code = self::PAYBOX_STATUSES[$resultCode] ?? self::PAYBOX_STATUSES[self::PAYBOX_UNKNOWN];

        return [
            'code' => $code, // error, timeout, invalid-card, paybox, donation_paybox_success
            'result' => $resultCode, // 00000, 00001, 00002
            'status' => self::PAYBOX_SUCCESS === $code ? self::RESULT_STATUS_EFFECTUE : self::RESULT_STATUS_ERREUR,
            'uuid' => $donationUuid,
            '_status_token' => (string) $this->tokenManager->getToken(self::STATUS_TOKEN),
        ];
    }

    public function buildDonationReference(UuidInterface $uuid, string $fullName): string
    {
        return \sprintf(
            '%s_%s',
            $uuid,
            $this->slugify->slugify($fullName)
        );
    }

    public function validateRetryToken(string $token): bool
    {
        return $this->tokenManager->isTokenValid(new CsrfToken(self::RETRY_TOKEN, $token));
    }

    private function validateCallback(string $token): void
    {
        if ($this->tokenManager->isTokenValid(new CsrfToken(self::CALLBACK_TOKEN, $token))) {
            return;
        }

        throw new InvalidDonationCallbackException();
    }

    public function generateRetryToken(): string
    {
        return $this->tokenManager->getToken(self::RETRY_TOKEN)->getValue();
    }
}
