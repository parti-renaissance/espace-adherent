<?php

namespace App\GoCardless;

use GoCardlessPro\Resources\Customer;
use GoCardlessPro\Resources\CustomerBankAccount;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\Subscription;

interface ClientInterface
{
    public function createCustomer(string $email, string $firstName, string $lastName, array $metadata = []): Customer;

    public function createBankAccount(Customer $customer, string $iban, string $accountName): CustomerBankAccount;

    public function createMandate(CustomerBankAccount $customerBankAccount): Mandate;

    public function createSubscription(Mandate $mandate, int $amount, array $metadata = []): Subscription;
}
