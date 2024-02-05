<?php

namespace App\Donation\Request;

use App\Address\GeoCoder;
use App\Controller\EnMarche\DonationController;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Transaction;
use App\Exception\InvalidDonationCallbackException;
use App\Exception\InvalidDonationPayloadException;
use App\Exception\InvalidDonationStatusException;
use App\Exception\InvalidPayboxPaymentSubscriptionValueException;
use Cocur\Slugify\SlugifyInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DonationRequestUtils
{
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
    private const SESSION_KEY = 'donation_request';

    private $validator;
    private $session;
    private $tokenManager;
    private $slugify;
    private $geocoder;

    public function __construct(
        ValidatorInterface $validator,
        SessionInterface $session,
        CsrfTokenManagerInterface $tokenManager,
        SlugifyInterface $slugify,
        GeoCoder $geocoder
    ) {
        $this->validator = $validator;
        $this->session = $session;
        $this->tokenManager = $tokenManager;
        $this->slugify = $slugify;
        $this->geocoder = $geocoder;
    }

    /**
     * @throws InvalidDonationPayloadException
     */
    public function createFromRequest(Request $request, ?Adherent $currentUser): DonationRequest
    {
        $duration = $this->getDuration($request);
        $amount = (float) $request->query->get('montant');

        /** @var DonationRequest $donation */
        if ($donation = $this->session->get(static::SESSION_KEY)) {
            $donation->setDuration($duration);
            $donation->setAmount($amount);

            return $donation;
        }

        $clientIp = $request->getClientIp();

        if (!PayboxPaymentSubscription::isValid($duration)) {
            throw new InvalidPayboxPaymentSubscriptionValueException($duration);
        }

        if ($currentUser) {
            $donation = DonationRequest::createFromAdherent($currentUser, $clientIp, $amount, $duration);
        } else {
            $donation = new DonationRequest($clientIp, $amount, $duration);
            $donation->getAddress()->setCountry($this->geocoder->getCountryCodeFromIp($clientIp));
        }

        if ($request->query->has(self::RETRY_PAYLOAD)) {
            return $this->hydrateFromRetryPayload($donation, $request->query->get(self::RETRY_PAYLOAD, '{}'));
        }

        return $donation;
    }

    public function getDuration(Request $request): int
    {
        return $request->query->getInt('abonnement', PayboxPaymentSubscription::NONE);
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

    public function startDonationRequest(DonationRequest $donationRequest): void
    {
        $this->session->set(static::SESSION_KEY, $donationRequest);
    }

    public function terminateDonationRequest(): void
    {
        $this->session->remove(static::SESSION_KEY);
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
            'status' => self::PAYBOX_SUCCESS === $code ? DonationController::RESULT_STATUS_EFFECTUE : DonationController::RESULT_STATUS_ERREUR,
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
