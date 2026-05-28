<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Adhesion\ActivationCodeManager;
use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Signup\SignupCode;
use App\Repository\AdherentActivationCodeRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class AdherentActivationCodeRepositoryTest extends AbstractKernelTestCase
{
    private const MAX_ATTEMPTS = ActivationCodeManager::MAX_FAILED_ATTEMPTS;

    /**
     * The brute-force lockout lives entirely in the incrementFailedAttempts DQL UPDATE
     * (CASE WHEN failedAttempts >= :max). The unit tests only assert the method is CALLED;
     * this pins the real SQL against the DB so the revoke-on-Nth-attempt behaviour — and the
     * MySQL left-to-right SET evaluation it depends on — cannot regress unnoticed.
     */
    public function testIncrementFailedAttemptsRevokesExactlyOnMaxAttempt(): void
    {
        $repository = $this->get(AdherentActivationCodeRepository::class);
        $code = $this->persistActivationCode();

        // Failures 1..MAX-1 must increment the counter but leave the code active.
        for ($attempt = 1; $attempt < self::MAX_ATTEMPTS; ++$attempt) {
            $repository->incrementFailedAttempts($code, self::MAX_ATTEMPTS);
            $this->manager->refresh($code);

            self::assertSame($attempt, $code->failedAttempts);
            self::assertFalse($code->isRevoked(), "Code must still be active after {$attempt} failed attempt(s).");
        }

        // The MAX-th failure must flip revokedAt within the same UPDATE.
        $repository->incrementFailedAttempts($code, self::MAX_ATTEMPTS);
        $this->manager->refresh($code);

        self::assertSame(self::MAX_ATTEMPTS, $code->failedAttempts);
        self::assertTrue($code->isRevoked(), 'Code must be revoked on exactly the MAX_FAILED_ATTEMPTS-th failure.');
    }

    private function persistActivationCode(): AdherentActivationCode
    {
        $email = 'activation-code-repo@example.test';
        $adherent = Adherent::create(
            Adherent::createUuid($email),
            substr(bin2hex(random_bytes(4)), 0, 7),
            $email,
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );
        $adherent->setStatus(Adherent::PENDING);

        $code = AdherentActivationCode::create($adherent, ActivationCodeManager::CODE_TTL, SignupCode::LENGTH);

        $this->manager->persist($adherent);
        $this->manager->persist($code);
        $this->manager->flush();

        return $code;
    }
}
