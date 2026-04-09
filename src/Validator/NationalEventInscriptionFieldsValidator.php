<?php

declare(strict_types=1);

namespace App\Validator;

use App\NationalEvent\DTO\InscriptionRequest;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NationalEventInscriptionFieldsValidator extends ConstraintValidator
{
    public function __construct(private readonly NationalEventRepository $nationalEventRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NationalEventInscriptionFields) {
            throw new UnexpectedTypeException($constraint, NationalEventInscriptionFields::class);
        }

        if (!$value instanceof InscriptionRequest) {
            throw new UnexpectedTypeException($value, InscriptionRequest::class);
        }

        $event = $this->nationalEventRepository->find($value->eventId);

        if (!$event) {
            return;
        }

        if ($event->showBirthPlace && $event->requiredBirthPlace && !$value->birthPlace) {
            $this->addViolation($constraint, 'birthPlace');
        }

        if ($event->phoneRequired && !$value->phone) {
            $this->addViolation($constraint, 'phone');
        }

        if ($event->showEmergencyContact && $event->requiredEmergencyContact) {
            if (!$value->emergencyContactName) {
                $this->addViolation($constraint, 'emergencyContactName');
            }
            if (!$value->emergencyContactPhone) {
                $this->addViolation($constraint, 'emergencyContactPhone');
            }
        }

        if ($event->showAccessibility && $event->requiredAccessibility && !$value->accessibility) {
            $this->addViolation($constraint, 'accessibility');
        }
    }

    private function addViolation(NationalEventInscriptionFields $constraint, string $path): void
    {
        $this->context->buildViolation($constraint->messageNotBlank)
            ->atPath($path)
            ->addViolation()
        ;
    }
}
