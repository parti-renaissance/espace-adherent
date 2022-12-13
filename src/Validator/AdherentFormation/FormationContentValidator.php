<?php

namespace App\Validator\AdherentFormation;

use App\Entity\AdherentFormation\Formation;
use App\Entity\AdherentFormation\FormationContentTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FormationContentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof FormationContent) {
            throw new UnexpectedTypeException($constraint, FormationContent::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Formation) {
            throw new UnexpectedValueException($value, Formation::class);
        }

        $contentType = $value->getContentType();

        if (null === $contentType) {
            return;
        }

        switch ($contentType) {
            case FormationContentTypeEnum::FILE:
                if (!$value->getFile()) {
                    $this
                        ->context
                        ->buildViolation($constraint->missingFileMessage)
                        ->atPath('file')
                        ->addViolation()
                    ;
                }

                break;
            case FormationContentTypeEnum::LINK:
                if (!$value->getFile()) {
                    $this
                        ->context
                        ->buildViolation($constraint->missingLinkMessage)
                        ->atPath('link')
                        ->addViolation()
                    ;
                }

                break;
        }
    }
}
