<?php

declare(strict_types=1);

namespace App\Admin\NationalEvent\JEM;

use App\Admin\NationalEvent\PaymentAdmin;
use App\NationalEvent\NationalEventTypeEnum;

class JEMPaymentAdmin extends PaymentAdmin
{
    protected $baseRoutePattern = 'meetings-jem/inscription-paiements';
    protected $baseRouteName = 'admin_app_nationalevent_nationalevent_jem_inscriptions_payments';

    protected function getAllowedEventTypes(): ?array
    {
        return [NationalEventTypeEnum::JEM];
    }

    protected function getForbiddenEventTypes(): ?array
    {
        return null;
    }
}
