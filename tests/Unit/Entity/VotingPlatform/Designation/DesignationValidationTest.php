<?php

declare(strict_types=1);

namespace Tests\App\Unit\Entity\VotingPlatform\Designation;

use App\Entity\VotingPlatform\Designation\Designation;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DesignationValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
        ;
    }

    public function testNotifyPotentialElectorateRejectsNonConsultationOrVoteType(): void
    {
        $designation = $this->createDesignation(DesignationTypeEnum::COMMITTEE_ADHERENT, 2026, true);

        $violations = $this->validator->validateProperty($designation, 'notifyPotentialElectorate', ['Default']);

        self::assertGreaterThan(0, $violations->count());
        self::assertStringContainsString('collège électoral potentiel', (string) $violations->get(0)->getMessage());
    }

    public function testNotifyPotentialElectorateRejectsMissingTargetYear(): void
    {
        $designation = $this->createDesignation(DesignationTypeEnum::CONSULTATION, null, true);

        $violations = $this->validator->validateProperty($designation, 'notifyPotentialElectorate', ['Default']);

        self::assertGreaterThan(0, $violations->count());
        self::assertStringContainsString('collège électoral potentiel', (string) $violations->get(0)->getMessage());
    }

    public function testNotifyPotentialElectorateAcceptsConsultationWithTargetYear(): void
    {
        $designation = $this->createDesignation(DesignationTypeEnum::CONSULTATION, 2026, true);

        $violations = $this->validator->validateProperty($designation, 'notifyPotentialElectorate', ['Default']);

        self::assertSame(0, $violations->count());
    }

    public function testNotifyPotentialElectorateAcceptsVoteWithTargetYear(): void
    {
        $designation = $this->createDesignation(DesignationTypeEnum::VOTE, 2026, true);

        $violations = $this->validator->validateProperty($designation, 'notifyPotentialElectorate', ['Default']);

        self::assertSame(0, $violations->count());
    }

    public function testNotifyPotentialElectorateFalseIgnoresOtherConstraints(): void
    {
        $designation = $this->createDesignation(DesignationTypeEnum::COMMITTEE_ADHERENT, null, false);

        $violations = $this->validator->validateProperty($designation, 'notifyPotentialElectorate', ['Default']);

        self::assertSame(0, $violations->count());
    }

    private function createDesignation(string $type, ?int $targetYear, bool $notifyPotential): Designation
    {
        $designation = new Designation('Test');
        $designation->setType($type);
        $designation->targetYear = $targetYear;
        $designation->notifyPotentialElectorate = $notifyPotential;

        return $designation;
    }
}
