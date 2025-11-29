<?php

declare(strict_types=1);

namespace App\Validator\AdherentFormation;

use App\Entity\AdherentFormation\Formation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class FormationContentValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
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

        if ($value->isLinkContent() && !$value->getLink()) {
            $this
                ->context
                ->buildViolation($constraint->missingLinkMessage)
                ->atPath('link')
                ->addViolation()
            ;
        }

        if ($value->isLinkContent() && $value->getFile()) {
            $this
                ->context
                ->buildViolation($constraint->linkWithFileMessage)
                ->atPath('file')
                ->addViolation()
            ;
        }
    }
}
