<?php

declare(strict_types=1);

namespace App\GoCardless;

use GoCardlessPro\Client as GoCardlessClient;
use GoCardlessPro\Environment;
use GoCardlessPro\Resources\Customer;
use GoCardlessPro\Resources\CustomerBankAccount;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\Subscription;

class Client implements ClientInterface
{
    private GoCardlessClient $client;

    public function __construct(
        private readonly string $goCardlessApiKey,
        private readonly string $goCardlessEnvironment,
    ) {
        $this->client = $this->createClient();
    }

    public function getCustomer(string $customerId): Customer
    {
        return $this->client->customers()->get($customerId);
    }

    public function createCustomer(
        string $email,
        string $firstName,
        string $lastName,
        ?string $address = null,
        ?string $city = null,
        ?string $postalCode = null,
        ?string $countryCode = null,
        array $metadata = [],
    ): Customer {
        return $this->client->customers()->create([
            'params' => [
                'email' => $email,
                'given_name' => $firstName,
                'family_name' => $lastName,
                'address_line1' => $address,
                'city' => $city,
                'postal_code' => $postalCode,
                'country_code' => $countryCode,
                'metadata' => $metadata,
            ],
        ]);
    }

    public function disableBankAccount(string $bankAccountId): CustomerBankAccount
    {
        return $this->client->customerBankAccounts()->disable($bankAccountId);
    }

    public function createBankAccount(
        Customer $customer,
        string $iban,
        string $accountName,
        array $metadata = [],
    ): CustomerBankAccount {
        return $this->client->customerBankAccounts()->create([
            'params' => [
                'iban' => $iban,
                'account_holder_name' => $accountName,
                'links' => ['customer' => $customer->id],
                'metadata' => $metadata,
            ],
        ]);
    }

    public function cancelMandate(string $mandateId): Mandate
    {
        return $this->client->mandates()->cancel($mandateId);
    }

    public function createMandate(CustomerBankAccount $customerBankAccount, array $metadata = []): Mandate
    {
        return $this->client->mandates()->create([
            'params' => [
                'links' => ['customer_bank_account' => $customerBankAccount->id],
                'metadata' => $metadata,
            ],
        ]);
    }

    public function cancelSubscription(string $subscriptionId): Subscription
    {
        return $this->client->subscriptions()->cancel($subscriptionId);
    }

    public function createSubscription(Mandate $mandate, int $amount, array $metadata = []): Subscription
    {
        return $this->client->subscriptions()->create([
            'params' => [
                'amount' => $amount * 100,
                'currency' => 'EUR',
                'name' => 'Cotisation élu',
                'interval_unit' => 'monthly',
                'day_of_month' => $this->getNextSubscriptionDay(),
                'links' => ['mandate' => $mandate->id],
                'metadata' => $metadata,
            ],
        ]);
    }

    /**
     * A subscription can start minimum 6 days after the current date.
     * The subscription should start at the 1st day of month or the 15th day of month.
     */
    private function getNextSubscriptionDay(): int
    {
        $nextPossibleDay = new \DateTime('+6 days')->format('d');

        return (int) $nextPossibleDay <= 15 ? 15 : 1;
    }

    private function createClient(): GoCardlessClient
    {
        return new GoCardlessClient([
            'access_token' => $this->goCardlessApiKey,
            'environment' => $this->getEnvironment(),
        ]);
    }

    private function getEnvironment(): string
    {
        return 'prod' === $this->goCardlessEnvironment
            ? Environment::LIVE
            : Environment::SANDBOX;
    }
}
