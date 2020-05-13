<?php

namespace Tests\App\Validator;

use App\Donation\DonationRequest;
use App\Validator\MaxMonthDonation;
use App\Validator\MaxMonthDonationValidator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MaxMonthDonationValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @dataProvider getDataDonation
     */
    public function testValidation($amount, int $subscription, int $violation = 0): void
    {
        $donationRequest = new DonationRequest(Uuid::uuid4(), '123.0.0.1', $amount, $subscription);
        $this->setObject($donationRequest);
        $this->validator->validate($donationRequest, new MaxMonthDonation());

        $this->assertSame(
            $violation,
            $violationsCount = \count($this->context->getViolations()),
            sprintf('%u violation expected. Got %u. for amount %u', $violation, $violationsCount, $amount)
        );
    }

    public function getDataDonation(): \Iterator
    {
        yield [50, 0, 0];
        yield [626, 0, 0];
        yield [626, 1, 1];
        yield [625, 1, 0];
        yield [625.00, 1, 0];
        yield [625.01, 1, 1];
    }

    protected function createValidator(): MaxMonthDonationValidator
    {
        return new MaxMonthDonationValidator();
    }
}
