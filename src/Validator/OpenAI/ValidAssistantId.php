<?php

namespace App\Validator\OpenAI;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class ValidAssistantId extends Constraint
{
    public string $errorMessage = 'admin.openai.assistant.not_valid';
}
