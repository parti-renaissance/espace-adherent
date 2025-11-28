<?php

declare(strict_types=1);

namespace App\Validator;

use App\Recaptcha\FriendlyCaptchaApiClient;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class Recaptcha extends Constraint
{
    public string $emptyMessage = 'common.recaptcha.empty_message';
    public string $message = 'common.recaptcha.invalid_message';
    public string $api = FriendlyCaptchaApiClient::NAME;

    public function __construct(
        ?string $api = null,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);

        $this->api = $api ?? $this->api;
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
