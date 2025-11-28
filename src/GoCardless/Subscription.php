<?php

declare(strict_types=1);

namespace App\GoCardless;

use GoCardlessPro\Resources\Customer;
use GoCardlessPro\Resources\CustomerBankAccount;
use GoCardlessPro\Resources\Mandate;
use GoCardlessPro\Resources\Subscription as GoCardlessSubscription;

class Subscription
{
    public function __construct(
        public Customer $customer,
        public CustomerBankAccount $bankAccount,
        public Mandate $mandate,
        public GoCardlessSubscription $subscription,
    ) {
    }
}
