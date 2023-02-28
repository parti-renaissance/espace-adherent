<?php

namespace App\GoCardless;

use GoCardlessPro\Client as GoCardlessClient;
use GoCardlessPro\Environment;
use GoCardlessPro\Resources\Customer;
use GoCardlessPro\Resources\CustomerBankAccount;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\Subscription;

class Client
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

    public function createBankAccount(Customer $customer, string $iban, string $accountName): CustomerBankAccount
    {
        return $this->client->customerBankAccounts()->create([
            'params' => [
                'iban' => $iban,
                'account_holder_name' => $accountName,
                'links' => ['customer' => $customer->id]],
        ]);
    }

    public function createMandate(CustomerBankAccount $customerBankAccount): Mandate
    {
        return $this->client->mandates()->create([
            'params' => [
                'links' => ['customer_bank_account' => $customerBankAccount->id]],
        ]);
    }

    public function createSubscription(Mandate $mandate, string $amount, array $metadata = []): Subscription
    {
        return $this->client->subscriptions()->create([
            'params' => [
                'amount' => $amount * 100,
                'currency' => 'EUR',
                'name' => 'Cotisation élu',
                'interval_unit' => 'monthly',
                'day_of_month' => 1,
                'metadata' => $metadata,
                'links' => ['mandate' => $mandate->id]],
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
