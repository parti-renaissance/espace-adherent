<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Exception\InvalidDonationCallbackException;
use AppBundle\Exception\InvalidDonationPayloadException;
use AppBundle\Exception\InvalidDonationStatusException;
use AppBundle\Exception\InvalidPayboxPaymentSubscriptionValueException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
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
        '00000' => self::PAYBOX_SUCCESS,

        // Platform or authorization center error
        '00001' => 'paybox',
        '00003' => 'paybox',

        // Invalid card number/validity
        '00004' => 'invalid-card',
        '00008' => 'invalid-card',
        '00021' => 'invalid-card',

        // Timeout
        '00030' => 'timeout',

        // Other
        self::PAYBOX_UNKNOWN => 'error',
    ];

    private $locator;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @throws InvalidDonationPayloadException
     */
    public function createFromRequest(Request $request, ?Adherent $currentUser): DonationRequest
    {
        $clientIp = $request->getClientIp();
        $amount = (float) $request->query->get('montant');
        $duration = $request->query->getInt('abonnement', PayboxPaymentSubscription::NONE);

        if (!PayboxPaymentSubscription::isValid($duration)) {
            throw new InvalidPayboxPaymentSubscriptionValueException($duration);
        }

        if ($currentUser) {
            $donation = DonationRequest::createFromAdherent($currentUser, $clientIp, $amount, $duration);
        } else {
            $donation = new DonationRequest(Uuid::uuid4(), $clientIp, $amount, $duration);
        }

        if ($request->query->has(self::RETRY_PAYLOAD)) {
            return $this->hydrateFromRetryPayload($donation, $request->query->get(self::RETRY_PAYLOAD, '{}'));
        }

        return $donation;
    }

    public function buildCallbackParameters()
    {
        return ['_callback_token' => $this->getTokenManager()->getToken(self::CALLBACK_TOKEN)];
    }

    public function extractPayboxResultFromCallBack(Request $request, string $token): array
    {
        $this->validateCallback($token);

        $data = array_merge($request->query->all(), [
            'authorization' => $request->query->get('authorization'),
            'result' => $request->query->get('result'),
        ]);

        unset($data['id'], $data['Sign']);

        return $data;
    }

    public function createRetryPayload(Donation $donation, Request $request): array
    {
        $this->validateCallbackStatus($request);

        $payload = $donation->getRetryPayload();
        $payload['_retry_token'] = (string) $this->getTokenManager()->getToken(self::RETRY_TOKEN);

        return [
            self::RETRY_PAYLOAD => json_encode($payload),
            'montant' => $donation->getAmountInEuros(),
        ];
    }

    public function createCallbackStatus(Donation $donation): array
    {
        $code = self::PAYBOX_STATUSES[$donation->getPayboxResultCode()] ?? self::PAYBOX_STATUSES[self::PAYBOX_UNKNOWN];

        return [
            'code' => $code,
            'uuid' => $donation->getUuid()->toString(),
            'status' => self::PAYBOX_SUCCESS === $code ? 'effectue' : 'erreur',
            '_status_token' => (string) $this->getTokenManager()->getToken(self::STATUS_TOKEN),
        ];
    }

    private function hydrateFromRetryPayload(DonationRequest $request, string $payload): DonationRequest
    {
        try {
            $data = \GuzzleHttp\json_decode(urldecode($payload), true);
        } catch (\InvalidArgumentException $e) {
            return $request;
        }

        $data = array_filter($data);
        if (!is_array($data) || !$data) {
            return $request;
        }

        $retry = $request->retryPayload($data);

        if ($this->validateRetryPayload($retry, $data['_retry_token'])) {
            return $retry;
        }

        return $request;
    }

    private function validateCallback(string $token): void
    {
        if ($this->getTokenManager()->isTokenValid(new CsrfToken(self::CALLBACK_TOKEN, $token))) {
            return;
        }

        throw new InvalidDonationCallbackException();
    }

    private function validateCallbackStatus(Request $request): void
    {
        if ($this->getTokenManager()->isTokenValid(new CsrfToken(self::STATUS_TOKEN, $request->query->get('_status_token')))
            && $this->isValidStatus($request->query->get('code'))) {
            return;
        }

        throw new InvalidDonationStatusException();
    }

    private function validateRetryPayload(DonationRequest $retry, string $token): bool
    {
        if ($this->getTokenManager()->isTokenValid(new CsrfToken(self::RETRY_TOKEN, $token))
        ) {
            return 0 === count($this->getValidator()->validate($retry));
        }

        throw new InvalidDonationPayloadException();
    }

    private function isValidStatus(string $status)
    {
        return in_array($status, self::PAYBOX_STATUSES, true);
    }

    private function getValidator(): ValidatorInterface
    {
        return $this->locator->get('validator');
    }

    private function getTokenManager(): CsrfTokenManagerInterface
    {
        return $this->locator->get('security.csrf.token_manager');
    }
}
