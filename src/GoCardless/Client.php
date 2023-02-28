<?php

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
        private readonly string $goCardlessEnvironment
    ) {
        $this->client = $this->createClient();
    }

    public function createCustomer(string $email, string $firstName, string $lastName, array $metadata = []): Customer
    {
        return $this->client->customers()->create([
            'params' => [
                'email' => $email,
                'given_name' => $firstName,
                'family_name' => $lastName,
                'metadata' => $metadata,
            ],
        ]);
    }

    public function createBankAccount(
        Customer $customer,
        string $iban,
        string $accountName,
        array $metadata = []
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

    public function createMandate(CustomerBankAccount $customerBankAccount, array $metadata = []): Mandate
    {
        return $this->client->mandates()->create([
            'params' => [
                'links' => ['customer_bank_account' => $customerBankAccount->id],
                'metadata' => $metadata,
            ],
        ]);
    }

    public function createSubscription(Mandate $mandate, int $amount, array $metadata = []): Subscription
    {
        return $this->client->subscriptions()->create([
            'params' => [
                'amount' => $amount * 100,
                'currency' => 'EUR',
                'name' => 'Cotisation élu',
                'interval_unit' => 'monthly',
                'links' => ['mandate' => $mandate->id],
                'metadata' => $metadata,
            ],
        ]);
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
            : Environment::SANDBOX
        ;
    }
}
