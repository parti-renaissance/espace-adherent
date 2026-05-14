<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign\Audience\Handler;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\Audience\Handler\ProcessAudienceChunkHandler;
use App\Mailchimp\Campaign\Audience\Message\FinalizeCampaignAudienceMessage;
use App\Mailchimp\Campaign\Audience\Message\ProcessAudienceChunkMessage;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Mailchimp\Campaign\MailchimpObjectIdMapping;
use App\Mailchimp\Driver;
use App\Repository\AdherentMessage\MailchimpStaticSegmentMemberRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ProcessAudienceChunkHandlerTest extends TestCase
{
    public function testNoCampaignFoundLogsAndReturns(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('find')->with(MailchimpCampaign::class, 99)->willReturn(null);
        $em->expects(self::never())->method('refresh');

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::never())->method('findPendingEmailsByChunk');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $this->createStub(Driver::class), $bus);
        $handler(new ProcessAudienceChunkMessage(99, 0));
    }

    public function testNoPendingRowsRetrySafeNoOp(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildPreparingCampaign($message, segmentId: 555);
        $this->setEntityId($campaign, 7);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::once())
            ->method('findPendingEmailsByChunk')
            ->with(4242, 3)
            ->willReturn([])
        ;
        $repo->expects(self::never())->method('markRowsAsProcessed');
        $repo->expects(self::never())->method('existsPending');

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::never())->method('send');

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $driver, $bus);
        $handler(new ProcessAudienceChunkMessage(7, 3));
    }

    public function testHappyPathDoesNotDispatchFinalizeIfPendingRemains(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildPreparingCampaign($message, segmentId: 555);
        $this->setEntityId($campaign, 7);

        $idToEmail = [10 => 'a@example.com', 11 => 'b@example.com'];

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $this->expectIncrementChunksDoneQuery($em);

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->expects(self::once())
            ->method('findPendingEmailsByChunk')
            ->with(4242, 0)
            ->willReturn($idToEmail)
        ;
        $repo->expects(self::once())
            ->method('markRowsAsProcessed')
            ->with(
                [
                    10 => SegmentMemberStatusEnum::Added,
                    11 => SegmentMemberStatusEnum::Added,
                ],
                [],
            )
        ;
        $repo->expects(self::once())
            ->method('existsPending')
            ->with(4242)
            ->willReturn(true)
        ;

        $response = $this->successfulResponse(['total_added' => 2, 'errors' => []]);
        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())
            ->method('send')
            ->with(
                'POST',
                '/lists/main-list/segments/555',
                ['members_to_add' => ['a@example.com', 'b@example.com']],
                false,
            )
            ->willReturn($response)
        ;

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $driver, $bus);
        $handler(new ProcessAudienceChunkMessage(7, 0));
    }

    public function testHappyPathDispatchesFinalizeWhenNoPendingRemains(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildPreparingCampaign($message, segmentId: 555);
        $this->setEntityId($campaign, 7);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $this->expectIncrementChunksDoneQuery($em);

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->method('findPendingEmailsByChunk')->willReturn([10 => 'last@example.com']);
        $repo->method('existsPending')->willReturn(false);

        $driver = $this->createMock(Driver::class);
        $driver->method('send')->willReturn($this->successfulResponse(['total_added' => 1, 'errors' => []]));

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (object $msg): bool {
                return $msg instanceof FinalizeCampaignAudienceMessage && 7 === $msg->mailchimpCampaignId;
            }))
            ->willReturn(new Envelope(new \stdClass()))
        ;

        $handler = $this->buildHandler($em, $repo, $driver, $bus);
        $handler(new ProcessAudienceChunkMessage(7, 0));
    }

    public function testRefusedEmailsAreMappedToRefusedStatus(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildPreparingCampaign($message, segmentId: 555);
        $this->setEntityId($campaign, 7);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);
        $this->expectIncrementChunksDoneQuery($em);

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->method('findPendingEmailsByChunk')->willReturn([
            10 => 'ok@example.com',
            11 => 'refused@example.com',
        ]);
        $repo->expects(self::once())
            ->method('markRowsAsProcessed')
            ->with(
                [
                    10 => SegmentMemberStatusEnum::Added,
                    11 => SegmentMemberStatusEnum::Refused,
                ],
                [
                    11 => 'unsubscribed',
                ],
            )
        ;
        $repo->method('existsPending')->willReturn(true);

        $driver = $this->createMock(Driver::class);
        $driver->method('send')->willReturn($this->successfulResponse([
            'total_added' => 1,
            'errors' => [
                ['email_address' => 'refused@example.com', 'error' => 'unsubscribed'],
            ],
        ]));

        $bus = $this->createMock(MessageBusInterface::class);
        $handler = $this->buildHandler($em, $repo, $driver, $bus);
        $handler(new ProcessAudienceChunkMessage(7, 0));
    }

    public function testHttpErrorThrowsForMessengerRetry(): void
    {
        $message = new AdherentMessage();
        $this->setEntityId($message, 100);
        $campaign = $this->buildPreparingCampaign($message, segmentId: 555);
        $this->setEntityId($campaign, 7);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($campaign);

        $repo = $this->createMock(MailchimpStaticSegmentMemberRepository::class);
        $repo->method('findPendingEmailsByChunk')->willReturn([10 => 'a@example.com']);
        $repo->expects(self::never())->method('markRowsAsProcessed');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getContent')->willReturn('boom');

        $driver = $this->createMock(Driver::class);
        $driver->method('send')->willReturn($response);

        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects(self::never())->method('dispatch');

        $handler = $this->buildHandler($em, $repo, $driver, $bus);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Mailchimp HTTP 500/');
        $handler(new ProcessAudienceChunkMessage(7, 0));
    }

    private function buildHandler(
        EntityManagerInterface $em,
        MailchimpStaticSegmentMemberRepository $repo,
        Driver $driver,
        MessageBusInterface $bus,
    ): ProcessAudienceChunkHandler {
        $mapping = $this->createStub(MailchimpObjectIdMapping::class);
        $mapping->method('getMainListId')->willReturn('main-list');

        return new ProcessAudienceChunkHandler($em, $repo, $driver, $mapping, $bus);
    }

    private function buildPreparingCampaign(AdherentMessage $message, int $segmentId): MailchimpCampaign
    {
        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);
        $campaign->setStaticSegmentId($segmentId);
        $segment = new MailchimpStaticSegment($campaign);
        $this->setEntityId($segment, 4242);
        $segment->mailchimpSegmentId = $segmentId;
        $campaign->setMailchimpStaticSegment($segment);
        $campaign->markAsPreparing($this->createStub(Adherent::class));

        return $campaign;
    }

    private function expectIncrementChunksDoneQuery(EntityManagerInterface $em): void
    {
        $query = $this->createMock(AbstractQuery::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('execute')->willReturn(1);
        $em->method('createQuery')->willReturn($query);
    }

    private function successfulResponse(array $body): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($body);

        return $response;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionObject($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }
}
