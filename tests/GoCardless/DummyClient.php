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
    public function getCustomer(string $customerId): Customer
    {
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
        return new Mandate((object) [
            'id' => $mandateId,
            'status' => 'cancelled',
        ]);
    }

    public function createMandate(CustomerBankAccount $customerBankAccount, array $metadata = []): Mandate
    {
        return new Mandate((object) [
            'id' => 'MD0123456',
            'links' => ['customer_bank_account' => $customerBankAccount->id],
            'metadata' => $metadata,
            'status' => 'active',
        ]);
    }

    public function cancelSubscription(string $subscriptionId): Subscription
    {
        return new Subscription((object) [
            'id' => $subscriptionId,
            'status' => 'cancelled',
        ]);
    }

    public function createSubscription(Mandate $mandate, int $amount, array $metadata = []): Subscription
    {
        return new Subscription((object) [
            'id' => 'SB0123456',
            'amount' => $amount * 100,
            'currency' => 'EUR',
            'name' => 'Cotisation élu',
            'interval_unit' => 'monthly',
            'day_of_month' => '1',
            'links' => ['mandate' => $mandate->id],
            'metadata' => $metadata,
            'status' => 'active',
        ]);
    }
}
