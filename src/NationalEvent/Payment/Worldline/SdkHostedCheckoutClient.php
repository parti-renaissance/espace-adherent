<?php

declare(strict_types=1);

namespace App\NationalEvent\Payment\Worldline;

use App\Entity\NationalEvent\Payment;
use OnlinePayments\Sdk\ClientInterface;
use OnlinePayments\Sdk\Domain\AmountOfMoney;
use OnlinePayments\Sdk\Domain\CardPaymentMethodSpecificInputBase;
use OnlinePayments\Sdk\Domain\ContactDetails;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequest;
use OnlinePayments\Sdk\Domain\Customer;
use OnlinePayments\Sdk\Domain\HostedCheckoutSpecificInput;
use OnlinePayments\Sdk\Domain\Order;
use OnlinePayments\Sdk\Domain\OrderReferences;
use OnlinePayments\Sdk\Domain\PaymentDetailsResponse;
use OnlinePayments\Sdk\Domain\PaymentResponse;
use OnlinePayments\Sdk\Domain\PersonalInformation;
use OnlinePayments\Sdk\Domain\PersonalName;

/**
 * Wraps the Worldline SDK so that no SDK type ever leaks outside of this class.
 */
class SdkHostedCheckoutClient implements HostedCheckoutClientInterface
{
    private const CURRENCY_CODE = 'EUR';
    private const LOCALE = 'fr_FR';

    /**
     * Direct sale: the capture is immediate, so a successful payment settles on statusCode 9 (payment requested)
     * instead of staying on 5 (authorised, awaiting a capture we never perform).
     */
    private const AUTHORIZATION_MODE_SALE = 'SALE';

    public function __construct(
        private readonly ClientInterface $client,
        private readonly string $worldlineMerchantId,
    ) {
    }

    public function createHostedCheckout(Payment $payment, string $returnUrl): CheckoutResult
    {
        $response = $this->client
            ->merchant($this->worldlineMerchantId)
            ->hostedCheckout()
            ->createHostedCheckout($this->buildCreateRequest($payment, $returnUrl))
        ;

        return new CheckoutResult(
            (string) $response->getHostedCheckoutId(),
            (string) $response->getRedirectUrl(),
            $response->getRETURNMAC(),
        );
    }

    public function getHostedCheckout(string $hostedCheckoutId): PaymentResult
    {
        $response = $this->client
            ->merchant($this->worldlineMerchantId)
            ->hostedCheckout()
            ->getHostedCheckout($hostedCheckoutId)
        ;

        return $this->buildPaymentResult($response->getCreatedPaymentOutput()?->getPayment());
    }

    public function getPaymentDetails(string $paymentId): PaymentResult
    {
        return $this->buildPaymentResult(
            $this->client->merchant($this->worldlineMerchantId)->payments()->getPaymentDetails($paymentId)
        );
    }

    private function buildCreateRequest(Payment $payment, string $returnUrl): CreateHostedCheckoutRequest
    {
        $inscription = $payment->inscription;

        $amountOfMoney = new AmountOfMoney();
        $amountOfMoney->setAmount($payment->amount);
        $amountOfMoney->setCurrencyCode(self::CURRENCY_CODE);

        $contactDetails = new ContactDetails();
        $contactDetails->setEmailAddress($inscription->addressEmail);

        $personalName = new PersonalName();
        $personalName->setFirstName($inscription->firstName);
        $personalName->setSurname($inscription->lastName);

        $personalInformation = new PersonalInformation();
        $personalInformation->setName($personalName);

        $customer = new Customer();
        $customer->setContactDetails($contactDetails);
        $customer->setPersonalInformation($personalInformation);

        // merchantReference carries our own payment uuid: it is what correlates the webhook back to the local payment.
        $references = new OrderReferences();
        $references->setMerchantReference($payment->getUuid()->toRfc4122());
        $references->setDescriptor($inscription->event->getSlug());

        $order = new Order();
        $order->setAmountOfMoney($amountOfMoney);
        $order->setCustomer($customer);
        $order->setReferences($references);

        $hostedCheckoutSpecificInput = new HostedCheckoutSpecificInput();
        $hostedCheckoutSpecificInput->setReturnUrl($returnUrl);
        $hostedCheckoutSpecificInput->setLocale(self::LOCALE);
        $hostedCheckoutSpecificInput->setSessionTimeout(self::SESSION_TIMEOUT_MINUTES);

        $cardPaymentMethodSpecificInput = new CardPaymentMethodSpecificInputBase();
        $cardPaymentMethodSpecificInput->setAuthorizationMode(self::AUTHORIZATION_MODE_SALE);

        $request = new CreateHostedCheckoutRequest();
        $request->setOrder($order);
        $request->setHostedCheckoutSpecificInput($hostedCheckoutSpecificInput);
        $request->setCardPaymentMethodSpecificInput($cardPaymentMethodSpecificInput);

        return $request;
    }

    private function buildPaymentResult(PaymentResponse|PaymentDetailsResponse|null $payment): PaymentResult
    {
        if (null === $payment) {
            return new PaymentResult(null, null, null, null, null);
        }

        $amountOfMoney = $payment->getPaymentOutput()?->getAmountOfMoney();

        return new PaymentResult(
            $payment->getId(),
            $payment->getStatusOutput()?->getStatusCode(),
            $payment->getStatus(),
            $amountOfMoney?->getAmount(),
            $amountOfMoney?->getCurrencyCode(),
            json_decode($payment->toJson(), true, 512, \JSON_THROW_ON_ERROR),
        );
    }
}
