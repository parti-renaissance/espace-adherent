<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use App\Repository\BannedAdherentRepository;
use App\Validator\Email\DisabledEmailValidator;
use App\Validator\Email\EmailForceableRequest;
use App\Validator\StrictEmail;
use App\Validator\StrictEmailValidator;
use Egulias\EmailValidator\Result\Reason\DomainAcceptsNoMail;
use Egulias\EmailValidator\Result\Reason\LocalOrReservedDomain;
use Egulias\EmailValidator\Result\Reason\NoDNSRecord;
use Egulias\EmailValidator\Result\Reason\UnableToGetDNSRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class StrictEmailValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): StrictEmailValidator
    {
        $bannedRepo = $this->createStub(BannedAdherentRepository::class);
        $bannedRepo->method('countForEmail')->willReturn(0);

        return new StrictEmailValidator(
            new DisabledEmailValidator($bannedRepo),
            new NullLogger(),
        );
    }

    public function testNullEmailDoesNotViolate(): void
    {
        $this->validator->validate(null, new StrictEmail(dnsCheck: false, disabledEmail: false));

        $this->assertNoViolation();
    }

    #[DataProvider('rfcInvalidEmails')]
    public function testRfcInvalidEmailViolates(string $email): void
    {
        $this->validator->validate($email, new StrictEmail(dnsCheck: false, disabledEmail: false, disposable: false));

        self::assertCount(1, $this->context->getViolations());
    }

    public static function rfcInvalidEmails(): \Iterator
    {
        yield ['not-an-email'];
        yield ['foo@'];
        yield ['@bar.com'];
        yield ['foo@bar..com'];
    }

    public function testDisposableEmailViolatesWhenEnabled(): void
    {
        $this->validator->validate('user@yopmail.com', new StrictEmail(dnsCheck: false, disabledEmail: false, disposable: true));

        self::assertCount(1, $this->context->getViolations());
    }

    public function testDisposableEmailDoesNotViolateWhenDisabled(): void
    {
        $this->validator->validate('user@yopmail.com', new StrictEmail(dnsCheck: false, disabledEmail: false, disposable: false));

        $this->assertNoViolation();
    }

    public function testValidEmailDoesNotViolate(): void
    {
        $this->validator->validate('user@example.com', new StrictEmail(dnsCheck: false, disabledEmail: false));

        $this->assertNoViolation();
    }

    public function testNonStringValueThrows(): void
    {
        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedValueException::class);

        $this->validator->validate(42, new StrictEmail(dnsCheck: false, disabledEmail: false));
    }

    public function testTypoCheckOffDoesNotFlagTypo(): void
    {
        $this->validator->validate('user@gmai.com', new StrictEmail(dnsCheck: false, disabledEmail: false, typoCheck: false));

        $this->assertNoViolation();
    }

    public function testTypoCheckOnFlagsTypoWithSuggestion(): void
    {
        $this->validator->validate('user@gmai.com', new StrictEmail(dnsCheck: false, disabledEmail: false, typoCheck: true));

        $this->buildViolation('Vouliez-vous dire « {{ suggestion }} » ?')
            ->setParameter('{{ email }}', 'user@gmai.com')
            ->setParameter('{{ suggestion }}', 'user@gmail.com')
            ->setCode('email_typo_suggestion')
            ->setCause(StrictEmail::LEVEL_ERROR)
            ->assertRaised()
        ;
    }

    public function testForceEmailBypassesTypoCheck(): void
    {
        $object = new readonly class implements EmailForceableRequest {
            public function isEmailForced(): bool
            {
                return true;
            }
        };

        $this->setObject($object);
        $this->validator->validate('user@gmai.com', new StrictEmail(dnsCheck: false, disabledEmail: false, typoCheck: true));

        $this->assertNoViolation();
    }

    public function testEmailForceableButNotForcedStillFlagsTypo(): void
    {
        $object = new readonly class implements EmailForceableRequest {
            public function isEmailForced(): bool
            {
                return false;
            }
        };

        $this->setObject($object);
        $this->validator->validate('user@gmai.com', new StrictEmail(dnsCheck: false, disabledEmail: false, typoCheck: true));

        self::assertCount(1, $this->context->getViolations());
    }

    public function testGetCodeFromReasonLocalOrReservedIsAlwaysWarning(): void
    {
        $constraint = new StrictEmail(strictDnsErrors: true);

        self::assertSame(
            StrictEmail::LEVEL_WARNING,
            $this->getCodeFromReason(new LocalOrReservedDomain(), $constraint),
        );
    }

    public function testGetCodeFromReasonUnableToGetDnsIsWarningEvenWithStrictDnsErrors(): void
    {
        $constraint = new StrictEmail(strictDnsErrors: true);

        // Critical: UnableToGetDNSRecord extends NoDNSRecord. Confirms instanceof order is correct.
        self::assertSame(
            StrictEmail::LEVEL_WARNING,
            $this->getCodeFromReason(new UnableToGetDNSRecord(), $constraint),
        );
    }

    public function testGetCodeFromReasonNoDnsRecordIsErrorWithStrictDnsErrors(): void
    {
        $constraint = new StrictEmail(strictDnsErrors: true);

        self::assertSame(
            StrictEmail::LEVEL_ERROR,
            $this->getCodeFromReason(new NoDNSRecord(), $constraint),
        );
    }

    public function testGetCodeFromReasonNoDnsRecordIsWarningWithoutStrictDnsErrors(): void
    {
        $constraint = new StrictEmail();

        self::assertSame(
            StrictEmail::LEVEL_WARNING,
            $this->getCodeFromReason(new NoDNSRecord(), $constraint),
        );
    }

    public function testGetCodeFromReasonDomainAcceptsNoMailIsErrorWithStrictDnsErrors(): void
    {
        $constraint = new StrictEmail(strictDnsErrors: true);

        self::assertSame(
            StrictEmail::LEVEL_ERROR,
            $this->getCodeFromReason(new DomainAcceptsNoMail(), $constraint),
        );
    }

    public function testTypoSuggestionIsLoggedWithMaskedEmail(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('info')
            ->with(
                'email_typo_suggestion',
                self::callback(static function (array $context): bool {
                    return 'u***@gmai.com' === $context['original']
                        && 'u***@gmail.com' === $context['suggestion'];
                }),
            )
        ;

        $bannedRepo = $this->createStub(BannedAdherentRepository::class);
        $bannedRepo->method('countForEmail')->willReturn(0);

        $validator = new StrictEmailValidator(new DisabledEmailValidator($bannedRepo), $logger);
        $validator->initialize($this->context);
        $validator->validate('user@gmai.com', new StrictEmail(dnsCheck: false, disabledEmail: false, typoCheck: true));
    }

    private function getCodeFromReason(\Egulias\EmailValidator\Result\Reason\Reason $reason, StrictEmail $constraint): string
    {
        // Promoted to protected in the validator: invoke via reflection rather than subclass for clarity.
        $method = new \ReflectionMethod(StrictEmailValidator::class, 'getCodeFromReason');

        return $method->invoke($this->validator, $reason, $constraint);
    }
}
