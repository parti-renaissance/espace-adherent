<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DonationFrequency extends Constraint
{
    public $message = 'La frequence de donation est incorrect';
    public $service = 'app.validator.donation_frequency';

    public function validatedBy(): string
    {
        return $this->service;
    }
}
