<?php

namespace App\Validator\TerritorialCouncil;

use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\TerritorialCouncil\Candidacy\SearchAvailableMembershipFilter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidSearchAvailableMembershipFilterValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidSearchAvailableMembershipFilter) {
            throw new UnexpectedTypeException($constraint, ValidSearchAvailableMembershipFilter::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof SearchAvailableMembershipFilter) {
            throw new UnexpectedTypeException($value, SearchAvailableMembershipFilter::class);
        }

        $quality = $value->getQuality();
        $query = $value->getQuery();

        if (\in_array($quality, $this->getElectedQualities(), true) && empty($query)) {
            $this->context
                ->buildViolation($constraint->messageEmptyQuery)
                ->atPath('quality')
                ->addViolation()
            ;
        }
    }

    private function getElectedQualities(): array
    {
        return [
            TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR,
            TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR,
            TerritorialCouncilQualityEnum::CITY_COUNCILOR,
            TerritorialCouncilQualityEnum::CONSULAR_CONSELOR,
        ];
    }
}
