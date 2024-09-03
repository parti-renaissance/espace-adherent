<?php

namespace App\Validator;

use App\ValueObject\Genders;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

#[\Attribute]
class CommitteeProvisionalSupervisor extends Constraint
{
    public $message = 'committee.provisional_supervisor.not_valid';
    public $notValidGenderMessage = 'committee.provisional_supervisor.gender.not_valid';

    public function __construct(
        public readonly string $errorPath,
        public readonly string $gender,
        ?string $message = null,
        $options = null,
        ?array $groups = null,
        $payload = null,
    ) {
        if (!\in_array($gender, Genders::MALE_FEMALE)) {
            throw new InvalidArgumentException('The "gender" parameter value is not valid.');
        }

        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }
}
