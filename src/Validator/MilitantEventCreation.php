<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MilitantEventCreation extends Constraint
{
    public string $notPureMilitantMessage = 'Seul un adhérent sans responsabilité cadre peut créer un événement militant.';
    public string $visibilityMessage = 'Un événement militant doit être public et répertorié.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
