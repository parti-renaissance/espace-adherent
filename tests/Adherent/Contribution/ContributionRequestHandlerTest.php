<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Contribution;

use App\Adherent\Contribution\ContributionRequestHandler;
use App\Contribution\ContributionTypeEnum;
use App\Entity\Adherent;
use App\Entity\Contribution\Contribution;
use App\GoCardless\ClientInterface;
use App\Repository\Contribution\ContributionRepository;
use Doctrine\ORM\EntityManagerInterface;
use GoCardlessPro\Core\Exception\ApiException;
use GoCardlessPro\Resources\Subscription as GoCardlessSubscription;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ContributionRequestHandlerTest extends TestCase
{
    private MockObject&ClientInterface $gocardless;
    private MockObject&ContributionRepository $contributionRepository;
    private ContributionRequestHandler $handler;

    protected function setUp(): void
    {
        $this->gocardless = $this->createMock(ClientInterface::class);
        $this->contributionRepository = $this->createMock(ContributionRepository::class);

        $bus = $this->createStub(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(
            static fn (object $message): Envelope => new Envelope($message),
        );

        $this->handler = new ContributionRequestHandler(
            $this->createStub(EntityManagerInterface::class),
            $this->gocardless,
            $this->contributionRepository,
            $bus,
        );
    }

    public function testHandleAmountChangeUpdatesExistingSubscription(): void
    {
        $adherentUuid = Uuid::uuid4();
        $adherent = $this->createAdherentWithUuid($adherentUuid);

        $lastContribution = $this->createExistingContribution('SB_existing');

        $this->contributionRepository
            ->expects(self::once())
            ->method('findLastAdherentContribution')
            ->with($adherent)
            ->willReturn($lastContribution)
        ;

        $updatedSubscription = $this->createGoCardlessSubscription('SB_existing', 'active');
        $this->gocardless
            ->expects(self::once())
            ->method('updateSubscriptionAmount')
            ->with('SB_existing', 50)
            ->willReturn($updatedSubscription)
        ;

        // Verify no cancel/create calls.
        $this->gocardless->expects(self::never())->method('cancelSubscription');
        $this->gocardless->expects(self::never())->method('createSubscription');
        $this->gocardless->expects(self::never())->method('cancelMandate');
        $this->gocardless->expects(self::never())->method('disableBankAccount');
        $this->gocardless->expects(self::never())->method('createMandate');
        $this->gocardless->expects(self::never())->method('createBankAccount');
        $this->gocardless->expects(self::never())->method('createCustomer');

        $returned = $this->handler->handleAmountChange($adherent, 50);

        self::assertSame($lastContribution, $returned);
        self::assertSame('SB_existing', $returned->gocardlessSubscriptionId);
        self::assertSame('active', $returned->gocardlessSubscriptionStatus);
    }

    public function testHandleAmountChangeReturnsNullWhenNoExistingContribution(): void
    {
        $adherent = $this->createAdherentWithUuid(Uuid::uuid4());

        $this->contributionRepository
            ->expects(self::once())
            ->method('findLastAdherentContribution')
            ->with($adherent)
            ->willReturn(null)
        ;

        $this->gocardless->expects(self::never())->method('updateSubscriptionAmount');

        self::assertNull($this->handler->handleAmountChange($adherent, 50));
    }

    public function testHandleAmountChangeFallsBackToCancelAndRecreateWhenAmendmentsLimitReached(): void
    {
        $adherentUuid = Uuid::uuid4();
        $adherent = $this->createAdherentWithUuid($adherentUuid);

        $lastContribution = $this->createExistingContribution('SB_existing');

        $this->contributionRepository
            ->expects(self::once())
            ->method('findLastAdherentContribution')
            ->with($adherent)
            ->willReturn($lastContribution)
        ;

        $this->gocardless
            ->expects(self::once())
            ->method('updateSubscriptionAmount')
            ->with('SB_existing', 50)
            ->willThrowException($this->createAmendmentsExceededException())
        ;

        $this->gocardless
            ->expects(self::once())
            ->method('cancelSubscription')
            ->with('SB_existing')
            ->willReturn($this->createGoCardlessSubscription('SB_existing', 'cancelled'))
        ;

        $this->gocardless
            ->expects(self::once())
            ->method('createSubscription')
            ->with('MD_existing', 50)
            ->willReturn($this->createGoCardlessSubscription('SB_new', 'active'))
        ;

        $returned = $this->handler->handleAmountChange($adherent, 50);

        self::assertNotNull($returned);
        self::assertSame('SB_new', $returned->gocardlessSubscriptionId);
        self::assertSame('MD_existing', $returned->gocardlessMandateId);
        self::assertSame('BA_existing', $returned->gocardlessBankAccountId);
        self::assertSame($returned, $adherent->getLastContribution());
    }

    public function testHandleAmountChangeRethrowsUnrelatedApiExceptions(): void
    {
        $adherent = $this->createAdherentWithUuid(Uuid::uuid4());
        $lastContribution = $this->createExistingContribution('SB_existing');

        $this->contributionRepository
            ->expects(self::once())
            ->method('findLastAdherentContribution')
            ->with($adherent)
            ->willReturn($lastContribution)
        ;

        $this->gocardless
            ->expects(self::once())
            ->method('updateSubscriptionAmount')
            ->with('SB_existing', 50)
            ->willThrowException($this->createApiExceptionWithReason('some_other_reason'))
        ;

        $this->gocardless->expects(self::never())->method('cancelSubscription');
        $this->gocardless->expects(self::never())->method('createSubscription');

        $this->expectException(ApiException::class);
        $this->handler->handleAmountChange($adherent, 50);
    }

    public function testHandleAmountChangeReturnsNullWhenContributionHasNoSubscriptionId(): void
    {
        $adherent = $this->createAdherentWithUuid(Uuid::uuid4());

        $orphanContribution = new Contribution();
        $orphanContribution->gocardlessSubscriptionId = null;

        $this->contributionRepository
            ->expects(self::once())
            ->method('findLastAdherentContribution')
            ->with($adherent)
            ->willReturn($orphanContribution)
        ;

        $this->gocardless->expects(self::never())->method('updateSubscriptionAmount');

        self::assertNull($this->handler->handleAmountChange($adherent, 50));
    }

    private function createAdherentWithUuid(UuidInterface $uuid): Adherent
    {
        $adherent = new Adherent();
        $reflection = new \ReflectionProperty(Adherent::class, 'uuid');
        $reflection->setValue($adherent, $uuid);

        return $adherent;
    }

    private function createExistingContribution(string $subscriptionId): Contribution
    {
        $contribution = new Contribution();
        $contribution->gocardlessCustomerId = 'CU_existing';
        $contribution->gocardlessBankAccountId = 'BA_existing';
        $contribution->gocardlessBankAccountEnabled = true;
        $contribution->gocardlessMandateId = 'MD_existing';
        $contribution->gocardlessMandateStatus = 'active';
        $contribution->gocardlessSubscriptionId = $subscriptionId;
        $contribution->gocardlessSubscriptionStatus = 'active';
        $contribution->type = ContributionTypeEnum::MANDATE;

        return $contribution;
    }

    private function createGoCardlessSubscription(string $id, string $status): GoCardlessSubscription
    {
        return new GoCardlessSubscription((object) [
            'id' => $id,
            'status' => $status,
        ]);
    }

    private function createAmendmentsExceededException(): ApiException
    {
        return $this->createApiExceptionWithReason('number_of_subscription_amendments_exceeded');
    }

    private function createApiExceptionWithReason(string $reason): ApiException
    {
        $apiResponse = (object) [
            'body' => (object) [
                'error' => (object) [
                    'code' => 422,
                    'message' => 'Validation failed',
                    'type' => 'validation_failed',
                    'errors' => [(object) ['reason' => $reason, 'message' => 'stub']],
                    'documentation_url' => '',
                    'request_id' => 'req_stub',
                ],
            ],
        ];

        return new ApiException($apiResponse);
    }
}
