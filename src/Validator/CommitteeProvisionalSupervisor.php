<?php

namespace App\Validator;

use App\ValueObject\Genders;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class CommitteeProvisionalSupervisor extends Constraint
{
    public $errorPath;
    public $gender;
    public $message = 'committee.provisional_supervisor.not_valid';
    public $notValidGenderMessage = 'committee.provisional_supervisor.gender.not_valid';

    public function __construct($options = null)
    {
        if (\is_array($options) && \array_key_exists('gender', $options) && !\in_array($options['gender'], Genders::MALE_FEMALE)) {
            throw new InvalidArgumentException('The "gender" parameter value is not valid.');
        }

        parent::__construct($options);
    }

    public function getRequiredOptions()
    {
        return ['errorPath', 'gender'];
    }
}
