<?php

declare(strict_types=1);

namespace Tests\App\Validator;

use App\Entity\Renaissance\NewsletterSubscription;
use App\Renaissance\Newsletter\SubscriptionRequest;
use App\Repository\Renaissance\NewsletterSubscriptionRepository;
use App\Validator\UniqueRenaissanceNewsletter;
use App\Validator\UniqueRenaissanceNewsletterValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class UniqueRenaissanceNewsletterValidatorTest extends ConstraintValidatorTestCase
{
    private NewsletterSubscriptionRepository&MockObject $repository;

    public function testConfirmedSubscriptionAddsViolation(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.test')
            ->willReturn($this->createSubscription(confirmed: true))
        ;

        $this->validator->validate($this->request('john@example.test'), new UniqueRenaissanceNewsletter());

        $this->buildViolation('newsletter.already_registered')->assertRaised();
    }

    public function testUnconfirmedSubscriptionDoesNotAddViolation(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.test')
            ->willReturn($this->createSubscription(confirmed: false))
        ;

        $this->validator->validate($this->request('john@example.test'), new UniqueRenaissanceNewsletter());

        $this->assertNoViolation();
    }

    public function testNoExistingSubscriptionDoesNotAddViolation(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('findOneByEmail')
            ->with('john@example.test')
            ->willReturn(null)
        ;

        $this->validator->validate($this->request('john@example.test'), new UniqueRenaissanceNewsletter());

        $this->assertNoViolation();
    }

    protected function createValidator(): UniqueRenaissanceNewsletterValidator
    {
        $this->repository = $this->createMock(NewsletterSubscriptionRepository::class);

        return new UniqueRenaissanceNewsletterValidator($this->repository);
    }

    private function request(string $email): SubscriptionRequest
    {
        $request = new SubscriptionRequest();
        $request->email = $email;

        return $request;
    }

    private function createSubscription(bool $confirmed): NewsletterSubscription
    {
        $request = new SubscriptionRequest();
        $request->email = 'john@example.test';
        $request->source = 'site_eu';

        $subscription = NewsletterSubscription::create($request);

        if ($confirmed) {
            $subscription->confirmedAt = new \DateTime();
        }

        return $subscription;
    }
}
