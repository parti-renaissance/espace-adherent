<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Donation;
use AppBundle\Exception\InvalidDonationPayloadException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zend\EventManager\Exception\InvalidCallbackException;

class DonationRequestUtils
{
    private const CALLBACK_TOKEN = 'donation_callback_token';
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
    public function createFromRequest(Request $request, float $amount, int $duration, ?Adherent $currentUser): DonationRequest
    {
        $clientIp = $request->getClientIp();

        if ($currentUser) {
            $donation = DonationRequest::createFromAdherent($currentUser, $clientIp, $amount, $duration);
        } else {
            $donation = new DonationRequest(Uuid::uuid4(), $clientIp, $amount, $duration);
        }

        if ($request->query->has(self::RETRY_PAYLOAD)) {
            return $this->hydrateFromRetryPayload($donation, $request->query->getAlnum(self::RETRY_PAYLOAD, '{}'));
        }

        return $donation;
    }

    public function createRetryPayload(Donation $donation, Request $request): array
    {
        $this->validateCallbackStatus($request);

        $payload = $donation->getRetryPayload();
        $payload['_token'] = $this->getTokenManager()->getToken(self::RETRY_TOKEN);

        return [
            self::RETRY_PAYLOAD => json_encode($payload),
            'montant' => $donation->getAmountInEuros(),
        ];
    }

    public function createCallbackStatus(Donation $donation): array
    {
        $code = self::PAYBOX_STATUSES[$donation->getPayboxResultCode()] ?? self::PAYBOX_UNKNOWN;

        return [
            'code' => $code,
            'uuid' => $donation->getUuid()->toString(),
            'status' => self::PAYBOX_SUCCESS === $code ? 'effectue' : 'erreur',
            '_token' => $this->getTokenManager()->getToken(self::CALLBACK_TOKEN),
        ];
    }

    private function hydrateFromRetryPayload(DonationRequest $request, string $payload): DonationRequest
    {
        $data = array_filter(json_decode($payload, true));

        if (!is_array($data) || !$data) {
            return $request;
        }

        $retry = $request->retryPayload($data);

        if ($this->validateRetryPayload($retry, $data)) {
            return $retry;
        }

        return $request;
    }

    private function validateCallbackStatus(Request $request): void
    {
        if ($this->getTokenManager()->isTokenValid(new CsrfToken(self::CALLBACK_TOKEN, $request->query->get(self::CALLBACK_TOKEN)))
            && isset(self::PAYBOX_STATUSES[$request->query->getAlnum('code')])
        ) {
            return;
        }

        throw new InvalidCallbackException();
    }

    private function validateRetryPayload(DonationRequest $retry, array $payload): bool
    {
        if (isset($payload[self::RETRY_TOKEN])
            && $this->getTokenManager()->isTokenValid(new CsrfToken(self::RETRY_TOKEN, $payload[self::RETRY_TOKEN]))
        ) {
            return 0 === count($this->getValidator()->validate($retry));
        }

        throw new InvalidDonationPayloadException();
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
