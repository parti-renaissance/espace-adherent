<?php

declare(strict_types=1);

namespace Tests\App\AdherentMessage\Handler;

use App\AdherentMessage\Handler\UpdatePublicationsStatsHandler;
use App\AdherentMessage\Stats\EmailAppHitWriter;
use App\DataFixtures\ORM\LoadAdherentMessageData;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Repository\AdherentMessage\PublicationStatisticsRepository;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Characterises the report→stats aggregation behaviour that survives the PublicationStatsRefresher
 * extraction: a SyncReportCommand step=3 on a sent message refreshes its PublicationStatistics from
 * the email hits recorded in app_hit.
 */
class UpdatePublicationsStatsHandlerTest extends AbstractKernelTestCase
{
    public function testStep3RefreshesPublicationStatisticsFromEmailHits(): void
    {
        $container = self::getContainer();
        $messageRepository = $container->get(AdherentMessageRepository::class);
        $statisticsRepository = $container->get(PublicationStatisticsRepository::class);
        $writer = $container->get(EmailAppHitWriter::class);
        $handler = $container->get(UpdatePublicationsStatsHandler::class);
        $adherent = $container->get(AdherentRepository::class)->findOneByEmail('adherent-male-a@en-marche-dev.fr');

        $message = $messageRepository->findOneByUuid(Uuid::fromString(LoadAdherentMessageData::MESSAGE_02_UUID));
        self::assertNotNull($message);
        self::assertTrue($message->isSent());

        $objectId = $message->getUuid()->toRfc4122();

        // Clean slate for this publication so the assertion is deterministic.
        $this->manager->getConnection()->executeStatement('DELETE FROM app_hit WHERE object_id = ?', [$objectId]);
        if ($existing = $statisticsRepository->findOneByMessage($message)) {
            $this->manager->remove($existing);
            $this->manager->flush();
        }

        // One email open recorded exactly as the SES/Mailchimp paths would.
        $writer->insertHits([$writer->buildOpenRow($adherent->getId(), $objectId, new \DateTimeImmutable('2024-05-01T10:00:00', new \DateTimeZone('UTC')))]);

        $handler(new SyncReportCommand($message->getUuid(), step: 3));

        $this->manager->clear();
        $refreshed = $messageRepository->findOneByUuid(Uuid::fromString(LoadAdherentMessageData::MESSAGE_02_UUID));
        $stats = $statisticsRepository->findOneByMessage($refreshed);

        self::assertNotNull($stats);
        self::assertGreaterThanOrEqual(1, $stats->uniqueOpensEmail);
    }
}
