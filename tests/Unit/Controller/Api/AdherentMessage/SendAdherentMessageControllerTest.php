<?php

declare(strict_types=1);

namespace Tests\App\Unit\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\MailchimpStatusEnum;
use App\Controller\Api\AdherentMessage\SendAdherentMessageController;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\PrepareResult;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

class SendAdherentMessageControllerTest extends TestCase
{
    private const int CAMPAIGN_ID = 4242;
    private const string EXPECTED_LOCK_KEY = 'adherent_message_send_4242';
    private const float EXPECTED_LOCK_TTL = 30.0;

    public function testPublicationLockAcquireFailReturnsConflict(): void
    {
        $message = $this->buildPublicationMessage();
        $adherent = new Adherent();

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects(self::once())->method('acquire')->with(false)->willReturn(false);
        $lock->expects(self::never())->method('release');

        $lockFactory = $this->buildLockFactoryReturning($lock);

        $preparer = $this->createMock(AudienceMessagePreparer::class);
        $preparer->expects(self::never())->method('prepare');

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::never())->method('sendPublication');

        $sendStatusFactory = $this->createMock(SendStatusFactory::class);
        $sendStatusFactory
            ->expects(self::once())
            ->method('build')
            ->with(self::isInstanceOf(MailchimpCampaign::class))
            ->willReturn(['preparation_status' => 'preparing']);

        $controller = new SendAdherentMessageController();
        $response = $controller->__invoke($manager, $preparer, $sendStatusFactory, $lockFactory, $message, $adherent);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertSame(
            ['status' => 'conflict', 'send_status' => ['preparation_status' => 'preparing']],
            json_decode((string) $response->getContent(), true),
        );
    }

    public function testPublicationHappyPathLocksThenPreparesAndSends(): void
    {
        $message = $this->buildPublicationMessage();
        $adherent = new Adherent();

        $callOrder = [];

        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects(self::once())
            ->method('acquire')
            ->with(false)
            ->willReturnCallback(static function () use (&$callOrder): bool {
                $callOrder[] = 'acquire';

                return true;
            });
        $lock
            ->expects(self::once())
            ->method('release')
            ->willReturnCallback(static function () use (&$callOrder): void {
                $callOrder[] = 'release';
            });

        $lockFactory = $this->buildLockFactoryReturning($lock);

        $preparer = $this->createMock(AudienceMessagePreparer::class);
        $preparer
            ->expects(self::once())
            ->method('prepare')
            ->with($message, $adherent)
            ->willReturnCallback(static function () use (&$callOrder): PrepareResult {
                $callOrder[] = 'prepare';

                return PrepareResult::preparing(['preparation_status' => 'preparing']);
            });

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager
            ->expects(self::once())
            ->method('sendPublication')
            ->with($message)
            ->willReturnCallback(static function () use (&$callOrder): void {
                $callOrder[] = 'sendPublication';
            });

        $sendStatusFactory = $this->createMock(SendStatusFactory::class);
        $sendStatusFactory
            ->expects(self::once())
            ->method('build')
            ->with(self::isInstanceOf(MailchimpCampaign::class))
            ->willReturn(['preparation_status' => 'preparing', 'can_send' => false]);

        $controller = new SendAdherentMessageController();
        $response = $controller->__invoke($manager, $preparer, $sendStatusFactory, $lockFactory, $message, $adherent);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(
            ['status' => 'sent', 'send_status' => ['preparation_status' => 'preparing', 'can_send' => false]],
            json_decode((string) $response->getContent(), true),
        );
        self::assertSame(['acquire', 'prepare', 'sendPublication', 'release'], $callOrder);
    }

    public function testPublicationLockReleasedEvenWhenPreparerThrows(): void
    {
        $message = $this->buildPublicationMessage();
        $adherent = new Adherent();

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects(self::once())->method('acquire')->with(false)->willReturn(true);
        $lock->expects(self::once())->method('release');

        $lockFactory = $this->buildLockFactoryReturning($lock);

        $preparer = $this->createMock(AudienceMessagePreparer::class);
        $preparer
            ->expects(self::once())
            ->method('prepare')
            ->with($message, $adherent)
            ->willThrowException(new \RuntimeException('broker down'));

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::never())->method('sendPublication');

        $sendStatusFactory = $this->createMock(SendStatusFactory::class);
        $sendStatusFactory->expects(self::never())->method('build');

        $controller = new SendAdherentMessageController();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('broker down');

        $controller->__invoke($manager, $preparer, $sendStatusFactory, $lockFactory, $message, $adherent);
    }

    public function testPublicationConflictFromPreparerStillReleasesLock(): void
    {
        $message = $this->buildPublicationMessage();
        $adherent = new Adherent();

        $lock = $this->createMock(SharedLockInterface::class);
        $lock->expects(self::once())->method('acquire')->with(false)->willReturn(true);
        $lock->expects(self::once())->method('release');

        $lockFactory = $this->buildLockFactoryReturning($lock);

        $preparer = $this->createMock(AudienceMessagePreparer::class);
        $preparer
            ->expects(self::once())
            ->method('prepare')
            ->with($message, $adherent)
            ->willReturn(PrepareResult::conflict(['preparation_status' => 'preparing']));

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::never())->method('sendPublication');

        $sendStatusFactory = $this->createMock(SendStatusFactory::class);
        $sendStatusFactory->expects(self::never())->method('build');

        $controller = new SendAdherentMessageController();
        $response = $controller->__invoke($manager, $preparer, $sendStatusFactory, $lockFactory, $message, $adherent);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        self::assertSame(
            ['status' => 'conflict', 'send_status' => ['preparation_status' => 'preparing']],
            json_decode((string) $response->getContent(), true),
        );
    }

    public function testStatutoryPathBypassesLock(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSynchronized')->willReturn(true);
        $message->method('getSubject')->willReturn('Statutory subject');
        $message->method('isSent')->willReturn(false);
        $message->method('getAuthor')->willReturn(null);
        $message->method('getSender')->willReturn(null);
        $message->method('isStatutory')->willReturn(true);

        $adherent = new Adherent();

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory->expects(self::never())->method('createLock');

        $preparer = $this->createMock(AudienceMessagePreparer::class);
        $preparer->expects(self::never())->method('prepare');

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager
            ->expects(self::once())
            ->method('getRecipients')
            ->with($message)
            ->willReturn([]);
        $manager
            ->expects(self::once())
            ->method('send')
            ->with($message, []);

        $sendStatusFactory = $this->createMock(SendStatusFactory::class);
        $sendStatusFactory->expects(self::never())->method('build');

        $controller = new SendAdherentMessageController();
        $response = $controller->__invoke($manager, $preparer, $sendStatusFactory, $lockFactory, $message, $adherent);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(['status' => 'sent'], json_decode((string) $response->getContent(), true));
    }

    public function testNotSynchronizedRejectsBeforeLock(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSynchronized')->willReturn(false);

        $this->assertGuardRejects($message, BadRequestHttpException::class, 'The message is not yet ready to send.');
    }

    public function testMissingSubjectRejectsBeforeLock(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSynchronized')->willReturn(true);
        $message->method('getSubject')->willReturn(null);

        $this->assertGuardRejects($message, BadRequestHttpException::class, 'Subject is required.');
    }

    /**
     * A statutory message owns no campaign to carry a send status, so isSent() stays its replay guard.
     */
    public function testAlreadySentStatutoryRejectsBeforeLock(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSynchronized')->willReturn(true);
        $message->method('getSubject')->willReturn('Subject');
        $message->method('isStatutory')->willReturn(true);
        $message->method('isSent')->willReturn(true);

        $this->assertGuardRejects($message, BadRequestHttpException::class, 'This message has been already sent.');
    }

    /**
     * A publication whose campaign already left, or is leaving, must not be sent twice — the campaign
     * status is what proves it, not isSent().
     */
    public function testPublicationWhoseCampaignAlreadyWentOutRejectsBeforeLock(): void
    {
        $message = $this->buildPublicationMessage(campaignStatus: MailchimpStatusEnum::Sent);

        $this->assertGuardRejects($message, BadRequestHttpException::class, 'This message has been already sent.');
    }

    public function testSandboxModeAdherentRejectsBeforeLock(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSynchronized')->willReturn(true);
        $message->method('getSubject')->willReturn('Subject');
        $message->method('isSent')->willReturn(false);
        $message->method('getAuthor')->willReturn(null);
        $message->method('getSender')->willReturn(null);

        $adherent = new Adherent();
        $adherent->sandboxMode = true;

        $this->assertGuardRejects($message, \RuntimeException::class, 'An error occurred. Please try again later.', $adherent);
    }

    public function testNoMailchimpCampaignRejectsBeforeLock(): void
    {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSynchronized')->willReturn(true);
        $message->method('getSubject')->willReturn('Subject');
        $message->method('isSent')->willReturn(false);
        $message->method('getAuthor')->willReturn(null);
        $message->method('getSender')->willReturn(null);
        $message->method('isStatutory')->willReturn(false);
        $message->method('getMailchimpCampaigns')->willReturn([]);

        $this->assertGuardRejects($message, BadRequestHttpException::class, 'No Mailchimp campaign attached to this message.');
    }

    private function assertGuardRejects(
        AdherentMessage $message,
        string $expectedException,
        string $expectedMessage,
        ?Adherent $adherent = null,
    ): void {
        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory->expects(self::never())->method('createLock');

        $preparer = $this->createMock(AudienceMessagePreparer::class);
        $preparer->expects(self::never())->method('prepare');

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects(self::never())->method('sendPublication');
        $manager->expects(self::never())->method('send');

        $sendStatusFactory = $this->createMock(SendStatusFactory::class);
        $sendStatusFactory->expects(self::never())->method('build');

        $controller = new SendAdherentMessageController();

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $controller->__invoke($manager, $preparer, $sendStatusFactory, $lockFactory, $message, $adherent ?? new Adherent());
    }

    /**
     * Build a Publication-flavored AdherentMessage stub with one MailchimpCampaign whose id is set.
     *
     * isSent defaults to true: a publication is marked sent the instant its author clicks, so that is the
     * normal state of every message reaching this endpoint for a replay.
     */
    private function buildPublicationMessage(
        MailchimpStatusEnum $campaignStatus = MailchimpStatusEnum::Save,
        bool $isSent = true,
    ): AdherentMessage {
        $message = $this->createStub(AdherentMessage::class);
        $message->method('isSynchronized')->willReturn(true);
        $message->method('getSubject')->willReturn('Subject');
        $message->method('isSent')->willReturn($isSent);
        $message->method('getAuthor')->willReturn(null);
        $message->method('getSender')->willReturn(null);
        $message->method('isStatutory')->willReturn(false);

        $campaign = new MailchimpCampaign($message);
        $campaign->status = $campaignStatus;
        $reflection = new \ReflectionObject($campaign);
        $property = $reflection->getProperty('id');
        $property->setValue($campaign, self::CAMPAIGN_ID);

        $message->method('getMailchimpCampaigns')->willReturn([$campaign]);

        return $message;
    }

    private function buildLockFactoryReturning(SharedLockInterface $lock): LockFactory&MockObject
    {
        $factory = $this->createMock(LockFactory::class);
        $factory
            ->expects(self::once())
            ->method('createLock')
            ->with(self::EXPECTED_LOCK_KEY, self::EXPECTED_LOCK_TTL)
            ->willReturn($lock);

        return $factory;
    }
}
