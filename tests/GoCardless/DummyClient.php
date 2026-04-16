<?php

declare(strict_types=1);

namespace Tests\App\GoCardless;

use App\GoCardless\ClientInterface;
use GoCardlessPro\Resources\Customer;
use GoCardlessPro\Resources\CustomerBankAccount;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\Subscription;

class DummyClient implements ClientInterface
{
    /** @var list<array{method: string, args: array<string, mixed>}> */
    public array $calls = [];

    public function getCustomer(string $customerId): Customer
    {
        $this->calls[] = ['method' => 'getCustomer', 'args' => ['customerId' => $customerId]];

        return new Customer((object) ['id' => $customerId]);
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
        $this->calls[] = ['method' => 'createCustomer', 'args' => [
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'metadata' => $metadata,
        ]];

        return new Customer((object) [
            'id' => 'CU0123456',
            'email' => $email,
            'given_name' => $firstName,
            'family_name' => $lastName,
            'address_line1' => $address,
            'city' => $city,
            'postal_code' => $postalCode,
            'country_code' => $countryCode,
            'metadata' => $metadata,
        ]);
    }

    public function disableBankAccount(string $bankAccountId): CustomerBankAccount
    {
        $this->calls[] = ['method' => 'disableBankAccount', 'args' => ['bankAccountId' => $bankAccountId]];

        return new CustomerBankAccount((object) [
            'id' => $bankAccountId,
            'enabled' => false,
        ]);
    }

    public function createBankAccount(
        Customer $customer,
        string $iban,
        string $accountName,
        array $metadata = [],
    ): CustomerBankAccount {
        $this->calls[] = ['method' => 'createBankAccount', 'args' => [
            'customerId' => $customer->id,
            'iban' => $iban,
            'accountName' => $accountName,
        ]];

        return new CustomerBankAccount((object) [
            'id' => 'BA0123456',
            'account_holder_name' => $accountName,
            'links' => ['customer' => $customer->id],
            'metadata' => $metadata,
            'enabled' => true,
        ]);
    }

    public function cancelMandate(string $mandateId): Mandate
    {
        $this->calls[] = ['method' => 'cancelMandate', 'args' => ['mandateId' => $mandateId]];

        return new Mandate((object) [
            'id' => $mandateId,
            'status' => 'cancelled',
        ]);
    }

    public function createMandate(CustomerBankAccount $customerBankAccount, array $metadata = []): Mandate
    {
        $this->calls[] = ['method' => 'createMandate', 'args' => ['bankAccountId' => $customerBankAccount->id]];

        return new Mandate((object) [
            'id' => 'MD0123456',
            'links' => ['customer_bank_account' => $customerBankAccount->id],
            'metadata' => $metadata,
            'status' => 'active',
        ]);
    }

    public function getSubscription(string $subscriptionId): Subscription
    {
        $this->calls[] = ['method' => 'getSubscription', 'args' => ['subscriptionId' => $subscriptionId]];

        return new Subscription((object) [
            'id' => $subscriptionId,
            'amount' => 5000,
            'status' => 'active',
        ]);
    }

    public function cancelSubscription(string $subscriptionId): Subscription
    {
        $this->calls[] = ['method' => 'cancelSubscription', 'args' => ['subscriptionId' => $subscriptionId]];

        return new Subscription((object) [
            'id' => $subscriptionId,
            'status' => 'cancelled',
        ]);
    }

    public function createSubscription(string $mandateId, int $amount, array $metadata = []): Subscription
    {
        $this->calls[] = ['method' => 'createSubscription', 'args' => [
            'mandateId' => $mandateId,
            'amount' => $amount,
            'metadata' => $metadata,
        ]];

        return new Subscription((object) [
            'id' => 'SB0123456',
            'amount' => $amount * 100,
            'currency' => 'EUR',
            'name' => 'Cotisation élu',
            'interval_unit' => 'monthly',
            'day_of_month' => '1',
            'links' => ['mandate' => $mandateId],
            'metadata' => $metadata,
            'status' => 'active',
        ]);
    }

    public function updateSubscriptionAmount(string $subscriptionId, int $amount): Subscription
    {
        $this->calls[] = ['method' => 'updateSubscriptionAmount', 'args' => [
            'subscriptionId' => $subscriptionId,
            'amount' => $amount,
        ]];

        return new Subscription((object) [
            'id' => $subscriptionId,
            'amount' => $amount * 100,
            'status' => 'active',
        ]);
    }
}
