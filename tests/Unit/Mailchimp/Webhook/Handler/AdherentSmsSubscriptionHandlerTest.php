<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Webhook\Handler;

use App\Entity\Adherent;
use App\Mailchimp\Contact\SmsOptOutSourceEnum;
use App\Mailchimp\Webhook\Handler\AdherentSmsSubscriptionHandler;
use App\Repository\AdherentRepository;
use App\Repository\SmsOptOutRepository;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdherentSmsSubscriptionHandlerTest extends TestCase
{
    private SubscriptionHandler&MockObject $subscriptionHandler;
    private SmsOptOutRepository&MockObject $smsOptOutRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private AdherentRepository&MockObject $adherentRepository;
    private AdherentSmsSubscriptionHandler $handler;

    protected function setUp(): void
    {
        $this->subscriptionHandler = $this->createMock(SubscriptionHandler::class);
        $this->smsOptOutRepository = $this->createMock(SmsOptOutRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->adherentRepository = $this->createMock(AdherentRepository::class);

        $this->handler = new AdherentSmsSubscriptionHandler(
            $this->subscriptionHandler,
            $this->smsOptOutRepository,
        );

        $this->handler->setEntityManager($this->entityManager);
        $this->handler->setAdherentRepository($this->adherentRepository);
    }

    public function testHandleOnSmsUnsubscribeWithPhoneCallsAdd(): void
    {
        $phone = $this->buildPhoneNumber('+33612345678');

        $adherent = $this->createMock(Adherent::class);
        $adherent->method('getSubscriptionTypeCodes')->willReturn([]);
        $adherent->method('getPhone')->willReturn($phone);

        $this->adherentRepository
            ->method('findOneByEmail')
            ->with('user@example.com')
            ->willReturn($adherent);

        $this->entityManager->expects($this->once())->method('refresh');

        $this->smsOptOutRepository
            ->expects($this->once())
            ->method('add')
            ->with('+33 6 12 34 56 78', SmsOptOutSourceEnum::Mailchimp)
        ;

        $this->handler->handle([
            'merges' => ['EMAIL' => 'user@example.com'],
            'subscription_status' => 'unsubscribed',
        ]);
    }

    public function testHandleOnSmsUnsubscribeWithoutPhoneDoesNotCallAdd(): void
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->method('getSubscriptionTypeCodes')->willReturn([]);
        $adherent->method('getPhone')->willReturn(null);

        $this->adherentRepository
            ->method('findOneByEmail')
            ->willReturn($adherent);

        $this->entityManager->expects($this->once())->method('refresh');
        $this->smsOptOutRepository->expects($this->never())->method('add');
        $this->smsOptOutRepository->expects($this->never())->method('cancelLastActiveOptOut');

        $this->handler->handle([
            'merges' => ['EMAIL' => 'user@example.com'],
            'subscription_status' => 'unsubscribed',
        ]);
    }

    public function testHandleOnSmsSubscribeCallsCancelActiveOptOuts(): void
    {
        $phone = $this->buildPhoneNumber('+33612345678');

        $adherent = $this->createMock(Adherent::class);
        $adherent->method('getSubscriptionTypeCodes')->willReturn([]);
        $adherent->method('getPhone')->willReturn($phone);

        $this->adherentRepository
            ->method('findOneByEmail')
            ->willReturn($adherent);

        $this->entityManager->expects($this->once())->method('refresh');
        $this->smsOptOutRepository->expects($this->never())->method('add');

        $this->smsOptOutRepository
            ->expects($this->once())
            ->method('cancelLastActiveOptOut')
            ->with('+33 6 12 34 56 78')
        ;

        $this->handler->handle([
            'merges' => ['EMAIL' => 'user@example.com'],
            'subscription_status' => 'subscribed',
        ]);
    }

    public function testHandleWithNoEmailReturnsEarly(): void
    {
        $this->adherentRepository->expects($this->never())->method('findOneByEmail');
        $this->subscriptionHandler->expects($this->never())->method('handleUpdateSubscription');
        $this->smsOptOutRepository->expects($this->never())->method('add');
        $this->smsOptOutRepository->expects($this->never())->method('cancelLastActiveOptOut');

        $this->handler->handle([
            'merges' => [],
            'subscription_status' => 'unsubscribed',
        ]);
    }

    private function buildPhoneNumber(string $e164): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber((int) ltrim(substr($e164, 3), '0'));

        return $phone;
    }
}
