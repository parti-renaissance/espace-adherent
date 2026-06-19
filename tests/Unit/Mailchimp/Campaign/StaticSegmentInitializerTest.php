<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Campaign;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Entity\AdherentMessage\MailchimpStaticSegment;
use App\Mailchimp\Campaign\StaticSegmentInitializer;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * The initializer has no Mailchimp dependency at all (only the EntityManager): "no Mailchimp call"
 * is therefore structural, not just asserted. These tests cover the local creation + idempotence.
 */
class StaticSegmentInitializerTest extends TestCase
{
    public function testCreatesLocalSegmentWithoutAnyMailchimpId(): void
    {
        $uuid = Uuid::v4();
        $campaign = $this->createCampaign($uuid);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(MailchimpStaticSegment::class));
        $em->expects(self::once())->method('flush');

        new StaticSegmentInitializer($em)->ensureLocalSegment($campaign);

        $segment = $campaign->getMailchimpStaticSegment();
        self::assertInstanceOf(MailchimpStaticSegment::class, $segment);
        self::assertSame($campaign, $segment->campaign);
        self::assertSame(\sprintf('PROD_%s', $uuid->toRfc4122()), $segment->name);
        // Local-only spine: no Mailchimp segment id on the entity, none on the campaign (vestigial).
        self::assertNull($segment->mailchimpSegmentId);
        self::assertNull($campaign->getStaticSegmentId());
    }

    public function testIsIdempotentWhenSegmentAlreadyExists(): void
    {
        $campaign = $this->createCampaign();
        $existing = new MailchimpStaticSegment($campaign);
        $campaign->setMailchimpStaticSegment($existing);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');

        new StaticSegmentInitializer($em)->ensureLocalSegment($campaign);

        self::assertSame($existing, $campaign->getMailchimpStaticSegment());
    }

    private function createCampaign(?Uuid $uuid = null): MailchimpCampaign
    {
        $message = $this->createStub(AdherentMessageInterface::class);
        $message->method('getUuid')->willReturn($uuid ?? Uuid::v4());

        return new MailchimpCampaign($message);
    }
}
