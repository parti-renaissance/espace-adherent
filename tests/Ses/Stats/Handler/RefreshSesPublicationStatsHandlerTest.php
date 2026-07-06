<?php

declare(strict_types=1);

namespace Tests\App\Ses\Stats\Handler;

use App\AdherentMessage\Stats\EmailAppHitWriter;
use App\DataFixtures\ORM\LoadAdherentMessageData;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\AdherentMessage\PublicationStatisticsRepository;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use App\Ses\Stats\Command\RefreshSesPublicationStatsCommand;
use App\Ses\Stats\Handler\RefreshSesPublicationStatsHandler;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional: SES engagement hits recorded in app_hit are aggregated into PublicationStatistics by
 * the SES-native handler — without touching the Mailchimp report API (MESSAGE_02 has no externalId,
 * so any Mailchimp poll would throw). Bot-burst clicks are flagged and excluded.
 */
class RefreshSesPublicationStatsHandlerTest extends AbstractKernelTestCase
{
    private const UTC = 'UTC';

    public function testAggregatesEngagementIntoPublicationStatisticsWithoutMailchimp(): void
    {
        $message = $this->message();
        $objectId = $message->getUuid()->toRfc4122();
        $adherent = $this->adherentRepository()->findOneByEmail('adherent-male-a@en-marche-dev.fr');

        $this->resetPublication($objectId, $message);

        $writer = self::getContainer()->get(EmailAppHitWriter::class);
        $writer->insertHits([
            $writer->buildOpenRow($adherent->getId(), $objectId, new \DateTimeImmutable('2024-05-01T10:00:00', new \DateTimeZone(self::UTC))),
            $writer->buildClickRow($adherent->getId(), $objectId, 'https://parti-renaissance.fr/a', new \DateTimeImmutable('2024-05-01T10:05:00', new \DateTimeZone(self::UTC))),
        ]);

        // autoReschedule:false: a test must not enter the self-rescheduling cadence.
        $this->handler()(new RefreshSesPublicationStatsCommand($message->getUuid(), false));

        $this->manager->clear();
        $stats = $this->statisticsRepository()->findOneByMessage($this->message());

        self::assertNotNull($stats);
        self::assertSame(1, $stats->uniqueOpensEmail);
        self::assertSame(1, $stats->uniqueClicksEmail);
        // Shadow reliability counters populate through the same refresh mapping (open + click both non-suspicious).
        self::assertSame(1, $stats->uniqueOpensEmailReliable);
        self::assertSame(1, $stats->uniqueOpensEmailEffective);
    }

    public function testMarksAndExcludesSuspiciousClickBurst(): void
    {
        $message = $this->message();
        $objectId = $message->getUuid()->toRfc4122();
        $bot = $this->adherentRepository()->findOneByEmail('adherent-male-a@en-marche-dev.fr');
        $human = $this->adherentRepository()->findOneByEmail('adherent-female-f@en-marche-dev.fr');

        $this->resetPublication($objectId, $message);

        $writer = self::getContainer()->get(EmailAppHitWriter::class);
        $sameSecond = new \DateTimeImmutable('2024-06-01T08:00:00', new \DateTimeZone(self::UTC));
        $writer->insertHits([
            $writer->buildClickRow($bot->getId(), $objectId, 'https://parti-renaissance.fr/a', $sameSecond),
            $writer->buildClickRow($bot->getId(), $objectId, 'https://parti-renaissance.fr/b', $sameSecond),
            $writer->buildClickRow($human->getId(), $objectId, 'https://parti-renaissance.fr/c', new \DateTimeImmutable('2024-06-01T09:30:00', new \DateTimeZone(self::UTC))),
        ]);

        $this->handler()(new RefreshSesPublicationStatsCommand($message->getUuid(), false));

        self::assertSame(2, $this->countSuspiciousClicks($objectId, $bot->getId()), 'the two same-second clicks are flagged');
        self::assertSame(0, $this->countSuspiciousClicks($objectId, $human->getId()), 'the isolated click is not flagged');

        $this->manager->clear();
        $stats = $this->statisticsRepository()->findOneByMessage($this->message());
        // Only the human's legit click is counted; the bot burst is excluded.
        self::assertSame(1, $stats->uniqueClicksEmail);
    }

    private function countSuspiciousClicks(string $objectId, int $adherentId): int
    {
        return (int) $this->manager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM app_hit WHERE object_id = ? AND adherent_id = ? AND event_type = ? AND source = ? AND suspicious = 1',
            [$objectId, $adherentId, 'click', 'email']
        );
    }

    private function resetPublication(string $objectId, AdherentMessage $message): void
    {
        $this->manager->getConnection()->executeStatement('DELETE FROM app_hit WHERE object_id = ?', [$objectId]);
        if ($existing = $this->statisticsRepository()->findOneByMessage($message)) {
            $this->manager->remove($existing);
            $this->manager->flush();
        }
    }

    private function message(): AdherentMessage
    {
        $message = $this->messageRepository()->findOneByUuid(Uuid::fromString(LoadAdherentMessageData::MESSAGE_02_UUID));
        self::assertInstanceOf(AdherentMessage::class, $message);
        self::assertTrue($message->isSent());

        return $message;
    }

    private function handler(): RefreshSesPublicationStatsHandler
    {
        return self::getContainer()->get(RefreshSesPublicationStatsHandler::class);
    }

    private function messageRepository(): AdherentMessageRepository
    {
        return self::getContainer()->get(AdherentMessageRepository::class);
    }

    private function statisticsRepository(): PublicationStatisticsRepository
    {
        return self::getContainer()->get(PublicationStatisticsRepository::class);
    }

    private function adherentRepository(): AdherentRepository
    {
        return self::getContainer()->get(AdherentRepository::class);
    }
}
