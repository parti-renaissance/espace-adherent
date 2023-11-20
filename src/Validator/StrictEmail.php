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

    public string $message = 'E-email adresse "{{ email }}" est invalide';

    public bool $disposable = true;
    public bool $disabledEmail = true;
}
