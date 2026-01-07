<?php

declare(strict_types=1);

namespace App\Validator;

use App\Form\NationalEvent\PackageField\PlaceChoiceFieldFormType;
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

        $keysToCheck = [];
        foreach ($configs as $config) {
            if (isset($config['options']) && \is_array($config['options'])) {
                foreach ($config['options'] as $option) {
                    if (!empty($option['quota'])) {
                        $keysToCheck[] = $config['cle'];
                        break;
                    }
                }
            }

            if (isset($config['type']) && PlaceChoiceFieldFormType::FIELD_NAME === $config['type']) {
                $keysToCheck[] = $config['cle'];
            }
        }
        $keysToCheck = array_unique($keysToCheck);

        $existingReservations = [];
        if (!empty($keysToCheck)) {
            $existingReservations = $this->inscriptionRepository->countPackageValues($event->getId(), $keysToCheck);
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
                    ->atPath("packageValues[$fieldKey]")
                    ->addViolation();

                continue;
            }

            if (empty($userValue)) {
                continue;
            }

            $maxQuota = null;
            $optionLabel = $userValue;

            if (isset($fieldConfig['options']) && \is_array($fieldConfig['options'])) {
                $selectedOptionConfig = null;
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

                if (!empty($selectedOptionConfig['quota'])) {
                    $maxQuota = (int) $selectedOptionConfig['quota'];
                    $optionLabel = $selectedOptionConfig['titre'] ?? $userValue;
                }
            } elseif (isset($fieldConfig['type']) && PlaceChoiceFieldFormType::FIELD_NAME === $fieldConfig['type']) {
                $maxQuota = 1;
            }

            if (null !== $maxQuota) {
                $currentUsage = $existingReservations[$fieldKey][$userValue] ?? 0;

                if ($currentUsage >= $maxQuota) {
                    $this->context->buildViolation($constraint->messageQuotaLimit)
                        ->setParameter('{{ option }}', $optionLabel)
                        ->atPath("packageValues[$fieldKey]")
                        ->addViolation();
                }
            }
        }
    }
}
