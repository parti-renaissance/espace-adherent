<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class StrictEmail extends Constraint
{
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';

    public string $errorMessage = "L'adresse « {{ email }} » n'est pas valide.";
    public string $warningMessage = "Nous ne sommes pas parvenus à vérifier l'existence de cette adresse. Vérifiez votre saisie, elle peut contenir une erreur. Si elle est correcte, ignorez cette alerte.";

    public bool $disposable = true;
    public bool $disabledEmail = true;
    public bool $dnsCheck = true;
    public bool $captainVerifyCheck = false;
}
