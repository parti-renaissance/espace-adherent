<?php

declare(strict_types=1);

namespace App\Validator;

use App\NationalEvent\DTO\InscriptionRequest;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NationalEventPackageValidator extends ConstraintValidator
{
    public function __construct(private readonly EventInscriptionRepository $inscriptionRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NationalEventPackage) {
            throw new UnexpectedTypeException($constraint, NationalEventPackage::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof InscriptionRequest) {
            throw new UnexpectedTypeException($value, InscriptionRequest::class);
        }

        $event = $value->event;

        if (!($configs = $event->packageConfig) || empty($submittedValues = $value->getPackageValues())) {
            return;
        }

        foreach ($configs as $fieldConfig) {
            $fieldKey = $fieldConfig['cle'];

            $userValue = $submittedValues[$fieldKey] ?? null;

            $isActive = true;

            if (!empty($fieldConfig['dependence'])) {
                $dependencyMet = false;

                foreach ($submittedValues as $submittedVal) {
                    if (\in_array($submittedVal, $fieldConfig['dependence'], true)) {
                        $dependencyMet = true;
                        break;
                    }
                }

                if (!$dependencyMet) {
                    $isActive = false;
                }
            }

            if (!$isActive) {
                if (!empty($userValue)) {
                    $this->context->buildViolation($constraint->messageDependencyError)
                        ->atPath("packageValues[$fieldKey]")
                        ->addViolation();
                }
                continue;
            }

            $isConfiguredRequired = $fieldConfig['required'] ?? true;

            if ($isConfiguredRequired && empty($userValue)) {
                $this->context->buildViolation($constraint->messageRequired)
                    // ->atPath("packageValues[$fieldKey]")
                    ->addViolation();

                continue;
            }

            if (empty($userValue)) {
                continue;
            }

            $selectedOptionConfig = null;

            if (isset($fieldConfig['options']) && \is_array($fieldConfig['options'])) {
                foreach ($fieldConfig['options'] as $option) {
                    if ((string) $option['id'] === (string) $userValue) {
                        $selectedOptionConfig = $option;
                        break;
                    }
                }

                if (!$selectedOptionConfig) {
                    $this->context->buildViolation($constraint->messageInvalidOption)
                        ->atPath("packageValues[$fieldKey]")
                        ->addViolation();

                    continue;
                }
            }

            if ($selectedOptionConfig && !empty($selectedOptionConfig['quota'])) {
                $maxQuota = (int) $selectedOptionConfig['quota'];

                $currentUsage = $this->inscriptionRepository->countPackageValueUsage(
                    $event,
                    $fieldKey,
                    $userValue
                );

                if ($currentUsage >= $maxQuota) {
                    $this->context->buildViolation($constraint->messageQuotaLimit)
                        ->setParameter('{{ option }}', $selectedOptionConfig['titre'] ?? $userValue)
                        ->atPath("packageValues[$fieldKey]")
                        ->addViolation();
                }
            }
        }
    }
}
