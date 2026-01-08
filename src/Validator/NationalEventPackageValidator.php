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

        if (!$configs = $event->packageConfig) {
            return;
        }

        $submittedValues = $value->getPackageValues();

        $keysToCheck = [];
        foreach ($configs as $config) {
            if (isset($config['options']) && \is_array($config['options'])) {
                foreach ($config['options'] as $option) {
                    if (\array_key_exists('quota', $option)) {
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

            $optionLabel = $userValue;
            $fieldReservations = $existingReservations[$fieldKey] ?? [];

            if (isset($fieldConfig['options']) && \is_array($fieldConfig['options'])) {
                $selectedOptionConfig = $this->findOptionConfig($userValue, $fieldConfig['options']);

                if (!$selectedOptionConfig) {
                    $this->context->buildViolation($constraint->messageInvalidOption)
                        ->atPath("packageValues[$fieldKey]")
                        ->addViolation();
                    continue;
                }

                if (!empty($selectedOptionConfig['dependence'])) {
                    $dependencyMet = false;

                    foreach ($submittedValues as $submittedVal) {
                        if (\in_array($submittedVal, $selectedOptionConfig['dependence'], true)) {
                            $dependencyMet = true;
                            break;
                        }
                    }

                    if (!$dependencyMet) {
                        $isActive = false;
                    }
                }

                if (!$isActive) {
                    $this->context->buildViolation($constraint->messageDependencyError)
                        ->atPath("packageValues[$fieldKey]")
                        ->addViolation();
                    continue;
                }

                if (\array_key_exists('quota', $selectedOptionConfig)) {
                    $optionLabel = $selectedOptionConfig['titre'] ?? $userValue;

                    $isQuotaExceeded = $this->checkQuotaExceeded(
                        $selectedOptionConfig,
                        $fieldConfig['options'],
                        $fieldReservations
                    );

                    if ($isQuotaExceeded) {
                        $this->context->buildViolation($constraint->messageQuotaLimit)
                            ->setParameter('{{ option }}', $optionLabel)
                            ->atPath("packageValues[$fieldKey]")
                            ->addViolation();
                    }
                }
            } elseif (isset($fieldConfig['type']) && PlaceChoiceFieldFormType::FIELD_NAME === $fieldConfig['type']) {
                $maxQuota = 1;
                $reservedByType = array_merge($fieldReservations, array_fill_keys($fieldConfig['places_reservees'] ?? [], 1));

                $currentUsage = $reservedByType[$userValue] ?? 0;

                if ($currentUsage >= $maxQuota) {
                    $this->context->buildViolation($constraint->messageQuotaLimit)
                        ->setParameter('{{ option }}', $optionLabel)
                        ->atPath("packageValues[$fieldKey]")
                        ->addViolation();
                }
            }
        }
    }

    private function checkQuotaExceeded(array $targetOption, array $allOptions, array $reservations): bool
    {
        if (\is_array($targetOption['quota'])) {
            foreach ($targetOption['quota'] as $depId) {
                $depConfig = $this->findOptionConfig($depId, $allOptions);

                if ($depConfig && \array_key_exists('quota', $depConfig)) {
                    if (is_numeric($depConfig['quota'])) {
                        $depMax = (int) $depConfig['quota'];
                        $depConsumed = $this->getConsumedCount($depId, $allOptions, $reservations);

                        if ($depConsumed >= $depMax) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        $max = (int) $targetOption['quota'];
        $consumed = $this->getConsumedCount($targetOption['id'] ?? $targetOption['titre'], $allOptions, $reservations);

        return $consumed >= $max;
    }

    private function getConsumedCount(string $targetId, array $allOptions, array $reservations): int
    {
        $count = $reservations[$targetId] ?? 0;

        foreach ($allOptions as $otherOption) {
            if (isset($otherOption['quota']) && \is_array($otherOption['quota'])) {
                if (\in_array($targetId, $otherOption['quota'], true)) {
                    $parentId = $otherOption['id'] ?? $otherOption['titre'];
                    $count += ($reservations[$parentId] ?? 0);
                }
            }
        }

        return $count;
    }

    private function findOptionConfig(string $value, array $options): ?array
    {
        foreach ($options as $option) {
            $id = $option['id'] ?? $option['titre'];
            if ((string) $id === $value) {
                return \is_string($option) ? ['id' => $option, 'titre' => $option] : $option;
            }
        }

        return null;
    }
}
