<?php

declare(strict_types=1);

namespace App\Pronostic;

enum PronosticReminderTypeEnum: string
{
    case CREATION = 'creation';
    case J_MINUS_1 = 'j_minus_1';
    case H_MINUS_1 = 'h_minus_1';
    case RESULTS = 'results';
}
