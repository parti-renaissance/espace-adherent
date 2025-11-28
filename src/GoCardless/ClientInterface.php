<?php

declare(strict_types=1);

namespace App\GoCardless;

use GoCardlessPro\Resources\Customer;
use GoCardlessPro\Resources\CustomerBankAccount;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\Subscription;

interface ClientInterface
{
    public function getCustomer(string $customerId): Customer;

    public function createCustomer(
        string $email,
        string $firstName,
        string $lastName,
        ?string $address = null,
        ?string $city = null,
        ?string $postalCode = null,
        ?string $countryCode = null,
        array $metadata = [],
    ): Customer;

    public function disableBankAccount(string $bankAccountId): CustomerBankAccount;

    public function createBankAccount(
        Customer $customer,
        string $iban,
        string $accountName,
        array $metadata = [],
    ): CustomerBankAccount;

    public function cancelMandate(string $mandateId): Mandate;

    public function createMandate(CustomerBankAccount $customerBankAccount, array $metadata = []): Mandate;

    public function cancelSubscription(string $subscriptionId): Subscription;

    public function createSubscription(Mandate $mandate, int $amount, array $metadata = []): Subscription;
}
