<?php

namespace App\Donation\Request;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Donation;
use App\Entity\Transaction;
use App\Exception\InvalidDonationCallbackException;
use App\Exception\InvalidDonationPayloadException;
use App\Exception\InvalidDonationStatusException;
use Cocur\Slugify\SlugifyInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DonationRequestUtils
{
    public const RESULT_STATUS_EFFECTUE = 'effectue';
    public const RESULT_STATUS_ERREUR = 'erreur';

    private const CALLBACK_TOKEN = 'donation_callback_token';
    private const STATUS_TOKEN = 'donation_status_token';
    private const RETRY_TOKEN = 'donation_retry_token';
    private const RETRY_PAYLOAD = 'donation_retry_payload';
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
        private readonly ValidatorInterface $validator,
        private readonly CsrfTokenManagerInterface $tokenManager,
        private readonly SlugifyInterface $slugify
    ) {
    }

    public function hasAmountAlert(?string $amount, int $subscription): bool
    {
        if (null === $amount) {
            return false;
        }

        return $amount > DonationRequest::ALERT_AMOUNT && PayboxPaymentSubscription::NONE === $subscription;
    }

    public function getDefaultConfirmSubscriptionAmount(string $amount): float
    {
        return round($amount / 5);
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

    public function createRetryPayload(Donation $donation, Request $request): array
    {
        $this->validateCallbackStatus($request);

        $payload = $donation->getRetryPayload();
        $payload['_retry_token'] = $this->generateRetryToken();

        return [
            self::RETRY_PAYLOAD => json_encode($payload),
            'montant' => $donation->getAmountInEuros(),
            'abonnement' => $donation->getDuration(),
        ];
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
        return sprintf(
            '%s_%s',
            $uuid,
            $this->slugify->slugify($fullName)
        );
    }

    public function hydrateFromRetryPayload(DonationRequest $request, string $payload): DonationRequest
    {
        try {
            $data = \GuzzleHttp\json_decode(urldecode($payload), true);
        } catch (\InvalidArgumentException $e) {
            return $request;
        }

        $data = array_filter($data);
        if (!\is_array($data) || !$data) {
            return $request;
        }

        $retry = $request->retryPayload($data);

        if ($this->validateRetryPayload($retry, $data['_retry_token'])) {
            return $retry;
        }

        return $request;
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

    private function validateCallbackStatus(Request $request): void
    {
        if ($this->tokenManager->isTokenValid(new CsrfToken(self::STATUS_TOKEN, $request->query->get('_status_token')))
            && $this->isValidStatus($request->query->get('code'))) {
            return;
        }

        throw new InvalidDonationStatusException();
    }

    private function validateRetryPayload(DonationRequest $retry, string $token): bool
    {
        if ($this->validateRetryToken($token)) {
            return 0 === \count($this->validator->validate($retry));
        }

        throw new InvalidDonationPayloadException();
    }

    private function isValidStatus(string $status): bool
    {
        return \in_array($status, self::PAYBOX_STATUSES, true);
    }

    public function generateRetryToken(): string
    {
        return $this->tokenManager->getToken(self::RETRY_TOKEN)->getValue();
    }
}
