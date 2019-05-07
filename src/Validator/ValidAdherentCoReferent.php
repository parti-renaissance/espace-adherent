<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidAdherentCoReferent extends Constraint
{
    public $messageInvalidAdherentEmail = 'referent.adherent.invalid_email';
    public $messageAdherentIsAlreadyCoReferent = 'referent.adherent.can_be_coreferent';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
