<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdherentActivationCodeTest extends TestCase
{
    public function testCreateAssignsAllFields(): void
    {
        $adherent = $this->createAdherent();

        $code = AdherentActivationCode::create($adherent, 15, 4);

        self::assertMatchesRegularExpression('/^\d{4}$/', $code->value);
        self::assertSame(0, $code->failedAttempts);
        self::assertNull($code->usedAt);
        self::assertNull($code->revokedAt);
    }

    public function testCreateSupportsThreeDigits(): void
    {
        $adherent = $this->createAdherent();

        $code = AdherentActivationCode::create($adherent, 10, 3);

        self::assertMatchesRegularExpression('/^\d{3}$/', $code->value);
    }

    public function testCreateAllowsDigitRepetitions(): void
    {
        $adherent = $this->createAdherent();

        // CSPRNG with repetitions allowed: produce many codes and assert at least one collision
        // between two positions to prove the generator does not enforce uniqueness.
        $hasRepetition = false;
        for ($i = 0; $i < 200; ++$i) {
            $value = AdherentActivationCode::create($adherent, 10, 4)->value;
            if (\strlen($value) !== \count(array_unique(str_split($value)))) {
                $hasRepetition = true;
                break;
            }
        }
        self::assertTrue($hasRepetition, 'Generator must allow digit repetitions (CSPRNG).');
    }

    #[DataProvider('provideInvalidLengths')]
    public function testCreateRejectsInvalidLength(int $length): void
    {
        $this->expectException(\InvalidArgumentException::class);

        AdherentActivationCode::create($this->createAdherent(), 10, $length);
    }

    public static function provideInvalidLengths(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'too long' => [11];
    }

    public function testIsExpiredReturnsTrueWhenPastTtl(): void
    {
        $adherent = $this->createAdherent();

        $code = AdherentActivationCode::create($adherent, -1, 4);

        self::assertTrue($code->isExpired());
    }

    public function testIsExpiredReturnsFalseWhenWithinTtl(): void
    {
        $adherent = $this->createAdherent();

        $code = AdherentActivationCode::create($adherent, 10, 4);

        self::assertFalse($code->isExpired());
    }

    public function testIsRevokedReturnsTrueWhenRevokedAtSet(): void
    {
        $adherent = $this->createAdherent();
        $code = AdherentActivationCode::create($adherent, 10, 4);

        self::assertFalse($code->isRevoked());

        $code->revokedAt = new \DateTime();

        self::assertTrue($code->isRevoked());
    }

    private function createAdherent(): Adherent
    {
        return Adherent::create(
            Adherent::createUuid('jane.doe@example.org'),
            'ABC-100',
            'jane.doe@example.org',
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );
    }
}
