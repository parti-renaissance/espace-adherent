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
    public string $warningMessage = "Nous ne sommes pas parvenus à vérifier l'existence de l'adresse « {{ email }} ». Vérifiez votre saisie avant de continuer.";

    public bool $disposable = true;
    public bool $disabledEmail = true;
    public bool $dnsCheck = true;
    public bool $captainVerifyCheck = false;
}
