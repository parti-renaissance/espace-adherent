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

    /**
     * Opt-in: suggest a popular-domain correction (e.g. gmai.com → gmail.com).
     * Default false to preserve the behavior of every existing consumer of StrictEmail.
     */
    public bool $typoCheck = false;

    /**
     * Opt-in: treat hard DNS failures (no MX/A, mail refused) as ERROR instead of WARNING.
     * Default false to preserve the legacy semantics consumed by /api/validate-email.
     */
    public bool $strictDnsErrors = false;

    public function __construct(
        ?bool $dnsCheck = true,
        ?bool $disabledEmail = true,
        ?bool $disposable = true,
        bool $typoCheck = false,
        bool $strictDnsErrors = false,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->dnsCheck = $dnsCheck;
        $this->disabledEmail = $disabledEmail;
        $this->disposable = $disposable;
        $this->typoCheck = $typoCheck;
        $this->strictDnsErrors = $strictDnsErrors;
    }
}
