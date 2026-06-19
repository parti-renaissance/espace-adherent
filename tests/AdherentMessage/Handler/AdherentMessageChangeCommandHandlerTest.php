<?php

declare(strict_types=1);

namespace Tests\App\AdherentMessage\Handler;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\AdherentMessage\Handler\AdherentMessageChangeCommandHandler;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\StaticSegmentInitializer;
use App\Repository\AdherentMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Since Phase 9 the handler makes ZERO Mailchimp calls: it has no Mailchimp dependency, only the
 * local segment initializer + the EntityManager. It ensures the local audience segment and flips
 * the message readiness flag.
 */
class AdherentMessageChangeCommandHandlerTest extends TestCase
{
    public function testEnsuresLocalSegmentAndFlipsFilterSync(): void
    {
        $uuid = Uuid::v4();
        $message = $this->createMessage();
        $campaign = $message->getMailchimpCampaigns()[0];

        self::assertFalse($message->isSynchronized(), 'A message with an unsynchronized filter is not ready yet.');

        $initializer = $this->createMock(StaticSegmentInitializer::class);
        $initializer->expects(self::once())->method('ensureLocalSegment')->with($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('flush');
        $em->expects(self::once())->method('clear');

        new AdherentMessageChangeCommandHandler($this->repository($uuid, $message), $initializer, $em)(
            new AdherentMessageChangeCommand($uuid)
        );

        self::assertTrue($message->getFilter()->isSynchronized());
        self::assertTrue($message->isSynchronized());
    }

    public function testEnsuresSegmentEvenWhenMessageAlreadySynchronized(): void
    {
        // A filter-less message with content + subject is already "synchronized", yet it still needs
        // its local segment for the send path: ensuring the segment must not be gated on readiness.
        $uuid = Uuid::v4();
        $message = $this->createMessage(withFilter: false);
        $campaign = $message->getMailchimpCampaigns()[0];

        self::assertTrue($message->isSynchronized());

        $initializer = $this->createMock(StaticSegmentInitializer::class);
        $initializer->expects(self::once())->method('ensureLocalSegment')->with($campaign);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush'); // nothing to flip → no readiness flush
        $em->expects(self::once())->method('clear');

        new AdherentMessageChangeCommandHandler($this->repository($uuid, $message), $initializer, $em)(
            new AdherentMessageChangeCommand($uuid)
        );
    }

    public function testReturnsEarlyWhenMessageNotFound(): void
    {
        $uuid = Uuid::v4();

        $repository = $this->createMock(AdherentMessageRepository::class);
        $repository->expects(self::once())->method('findOneByUuid')->with($uuid->toRfc4122())->willReturn(null);

        $initializer = $this->createMock(StaticSegmentInitializer::class);
        $initializer->expects(self::never())->method('ensureLocalSegment');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('flush');
        $em->expects(self::never())->method('clear');

        new AdherentMessageChangeCommandHandler($repository, $initializer, $em)(new AdherentMessageChangeCommand($uuid));
    }

    private function createMessage(bool $withFilter = true): AdherentMessage
    {
        $message = new AdherentMessage(Uuid::v4(), $this->createStub(Adherent::class));
        $message->setSubject('Subject');
        $message->setContent('Content');
        $message->addMailchimpCampaign(new MailchimpCampaign($message));

        if ($withFilter) {
            $message->setFilter(new AdherentMessageFilter());
        }

        return $message;
    }

    private function repository(Uuid $uuid, AdherentMessage $message): AdherentMessageRepository
    {
        $repository = $this->createMock(AdherentMessageRepository::class);
        $repository->expects(self::once())->method('findOneByUuid')->with($uuid->toRfc4122())->willReturn($message);

        return $repository;
    }
}
