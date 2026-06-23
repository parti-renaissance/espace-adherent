<?php

declare(strict_types=1);

namespace Tests\App\Repository\AdherentMessage;

use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\PublicationStatistics;
use App\Repository\AdherentMessage\PublicationStatisticsRepository;
use App\Repository\AdherentRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * Integration: the rebranched KPI aggregates email engagement from adherent_message_statistics
 * (channel-agnostic), recomputing rates from the raw counters — not from the stored *Rate fields,
 * whose denominator is opens rather than recipients.
 */
class PublicationStatisticsKpiTest extends AbstractKernelTestCase
{
    private const SCOPE = 'test_kpi_scope_phase8';

    public function testNationalReportRatioRecomputesRatesFromRawCounts(): void
    {
        $author = self::getContainer()->get(AdherentRepository::class)->findOneByEmail('adherent-male-a@en-marche-dev.fr');

        // Two sent publications in the same (unique) scope; ratios are invariant to test repetition.
        $this->seedPublication($author, opens: 30, clicks: 10, emails: 100, unsubscribed: 4);
        $this->seedPublication($author, opens: 20, clicks: 5, emails: 100, unsubscribed: 1);
        $this->manager->flush();

        $ratio = $this->repository()->findNationalReportRatio(self::SCOPE);

        // opens 50 / emails 200 = 0.25 ; clicks 15 / 200 = 0.075 ; unsub 5 / 200 = 0.025
        // (the bogus stored *Rate of 99/88/77 must NOT leak in).
        self::assertEqualsWithDelta(0.25, (float) $ratio['opened_rate'], 0.0001);
        self::assertEqualsWithDelta(0.075, (float) $ratio['clicked_rate'], 0.0001);
        self::assertEqualsWithDelta(0.025, (float) $ratio['unsubscribed_rate'], 0.0001);
    }

    public function testLocalReportRatioQueryExecutes(): void
    {
        // The 'local' KPI is requested on every call; assert the zone-joined aggregate query is valid.
        $result = $this->repository()->findLocalReportRatio(self::SCOPE, [], 30);

        self::assertArrayHasKey('opened_rate', $result);
        self::assertArrayHasKey('clicked_rate', $result);
        self::assertArrayHasKey('nb_campaigns', $result);
    }

    private function seedPublication(Adherent $author, int $opens, int $clicks, int $emails, int $unsubscribed): void
    {
        $message = new AdherentMessage(null, $author);
        $message->setSubject('KPI test');
        $message->setContent('<p>x</p>');
        $message->setInstanceScope(self::SCOPE);
        $message->markAsSent();
        $this->manager->persist($message);

        $statistics = new PublicationStatistics($message);
        $statistics->uniqueOpensEmail = $opens;
        $statistics->uniqueClicksEmail = $clicks;
        $statistics->uniqueEmails = $emails;
        $statistics->unsubscribed = $unsubscribed;
        // Deliberately inconsistent stored rates: the KPI must ignore them and recompute from counts.
        $statistics->uniqueOpensEmailRate = 99.0;
        $statistics->uniqueClicksEmailRate = 88.0;
        $statistics->unsubscribedRate = 77.0;
        $this->manager->persist($statistics);
    }

    private function repository(): PublicationStatisticsRepository
    {
        return self::getContainer()->get(PublicationStatisticsRepository::class);
    }
}
