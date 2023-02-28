<?php

namespace Tests\App\GoCardless;

use App\GoCardless\ClientInterface;
use GoCardlessPro\Resources\Customer;
use GoCardlessPro\Resources\CustomerBankAccount;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\Subscription;

class DummyClient implements ClientInterface
{
    public function createCustomer(string $email, string $firstName, string $lastName, array $metadata = []): Customer
    {
        return new Customer((object) ['id' => 'customer_123']);
    }

    public function createBankAccount(Customer $customer, string $iban, string $accountName): CustomerBankAccount
    {
        return new CustomerBankAccount((object) []);
    }

    public function createMandate(CustomerBankAccount $customerBankAccount): Mandate
    {
        return new Mandate((object) []);
    }

    public function createSubscription(Mandate $mandate, int $amount, array $metadata = []): Subscription
    {
        return new Subscription((object) []);
    }
}
