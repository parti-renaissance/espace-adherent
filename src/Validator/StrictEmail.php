<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class StrictEmail extends Constraint
{
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';

    public string $errorMessage = "L'adresse « {{ email }} » n'est pas valide.";
    public string $warningMessage = "Nous ne sommes pas parvenus à vérifier l'existence de cette adresse. Vérifiez votre saisie, elle peut contenir une erreur. Si elle est correcte, ignorez cette alerte.";

    public bool $disposable = true;
    public bool $disabledEmail = true;
    public bool $dnsCheck = true;

    public function __construct(
        ?bool $dnsCheck = true,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->dnsCheck = $dnsCheck;
    }
}
