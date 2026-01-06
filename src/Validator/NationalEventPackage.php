<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NationalEventPackage extends Constraint
{
    public string $messageRequired = 'Veuillez sélectionner une option.';
    public string $messageInvalidOption = 'L\'option sélectionnée n\'est pas valide ou n\'est plus disponible.';
    public string $messageDependencyError = 'Ce champ ne peut pas être rempli avec votre sélection actuelle.';
    public string $messageQuotaLimit = 'Le quota de places pour "{{ option }}" est atteint.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
