<?php

declare(strict_types=1);

namespace Tests\App\Ses\Webhook;

use App\AdherentMessage\Stats\EmailAppHitWriter;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Membership\ActivityPositionsEnum;
use App\Scope\ScopeEnum;
use App\Ses\Webhook\AppleEgressCidrProvider;
use App\Ses\Webhook\Processor\EngagementProcessor;
use App\Ses\Webhook\SesEngagementParser;
use App\Ses\Webhook\SesEventTargetResolver;
use App\Ses\Webhook\SesOpenReliabilityClassifier;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Tests\App\AbstractKernelTestCase;

/**
 * Real wiring of the engagement consumer: a SES OPEN event (SNS payload) is attributed to the recipient and
 * written to app_hit, with the reliability classifier marking machine-probable opens (Apple relay egress IP)
 * as suspicious while always recording the row. The non-egress and CLICK paths go through the actual
 * container-wired processor; the egress-match cases override only the deploy-time egress list and keep the
 * real parser, resolver, writer and DB — a mock-based test would prove none of this.
 */
#[Group('functional')]
class EngagementProcessorTest extends AbstractKernelTestCase
{
    private string $egressFile;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Deploy-time egress list, provided here via a temp file (mirrors the mounted ConfigMap in prod).
        $this->egressFile = tempnam(sys_get_temp_dir(), 'egress');
        file_put_contents($this->egressFile, "192.0.2.0/24\n"); // TEST-NET-1, matches the test fetch IP below
    }

    protected function tearDown(): void
    {
        @unlink($this->egressFile);
        $this->seq = 0;

        parent::tearDown();
    }

    public function testMachineProbableOpenIsWrittenAndMarkedSuspicious(): void
    {
        [$message, $recipient] = $this->createSentCampaign();

        $this->processOpenWithDetector($message->getUuid(), $recipient->getUuid(), '192.0.2.10', 'Mozilla/5.0');

        $row = $this->reloadOpenHit($recipient->getId(), $message->getUuid()->toRfc4122());
        self::assertSame(1, (int) $row['suspicious']);

        $raw = json_decode((string) $row['raw'], true);
        self::assertSame('unreliable', $raw['reliability']);
        self::assertSame('v1', $raw['detector']);
        self::assertSame(['ip_egress'], $raw['matched']);
        self::assertSame('192.0.2.10', $raw['ip_address'], 'The raw fetch IP is stored for debugging.');
        self::assertSame('Mozilla/5.0', $raw['user_agent'], 'The raw UA is stored verbatim for debugging.');
    }

    public function testHumanOpenIsWrittenUnmarked(): void
    {
        [$message, $recipient] = $this->createSentCampaign();

        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15';
        $this->processOpenWithDetector($message->getUuid(), $recipient->getUuid(), '198.51.100.7', $ua);

        $row = $this->reloadOpenHit($recipient->getId(), $message->getUuid()->toRfc4122());
        self::assertSame(0, (int) $row['suspicious']);

        $raw = json_decode((string) $row['raw'], true);
        self::assertSame('reliable', $raw['reliability']);
        self::assertSame([], $raw['matched']);
        self::assertSame($ua, $raw['user_agent'], 'The full client UA is stored verbatim for debugging.');
    }

    public function testOpenWithoutFetchIpIsClassifiedUnknownAndUnmarked(): void
    {
        [$message, $recipient] = $this->createSentCampaign();

        $this->processOpenWithDetector($message->getUuid(), $recipient->getUuid(), null, 'Mozilla/5.0');

        $row = $this->reloadOpenHit($recipient->getId(), $message->getUuid()->toRfc4122());
        self::assertSame(0, (int) $row['suspicious'], 'A missing fetch IP must never be marked suspicious.');

        $raw = json_decode((string) $row['raw'], true);
        self::assertSame('unknown', $raw['reliability']);
        self::assertNull($raw['ip_address'], 'A missing fetch IP is stored as null, not fabricated.');
    }

    public function testRealWiringRecordsAndClassifiesTheOpenUnflaggedForANonEgressIp(): void
    {
        [$message, $recipient] = $this->createSentCampaign();

        // Real container-wired processor with the deploy-time egress list: a documentation-range IP is never a
        // real Apple egress, so the open is recorded, its provenance stored, and it stays unflagged.
        $this->processWithRealWiring($this->openPayload($message->getUuid(), $recipient->getUuid(), '203.0.113.9', 'Mozilla/5.0'));

        $row = $this->reloadOpenHit($recipient->getId(), $message->getUuid()->toRfc4122());
        self::assertSame(0, (int) $row['suspicious'], 'A non-egress IP must never be flagged.');
        self::assertSame('reliable', json_decode((string) $row['raw'], true)['reliability']);
    }

    public function testClickIsRecordedThroughRealWiring(): void
    {
        [$message, $recipient] = $this->createSentCampaign();

        $this->processWithRealWiring($this->clickPayload($message->getUuid(), $recipient->getUuid(), 'https://parti-renaissance.fr/a'));

        $row = $this->manager->getConnection()->createQueryBuilder()
            ->select('suspicious', 'target_url')
            ->from('app_hit')
            ->where('adherent_id = :aid')
            ->andWhere('object_id = :oid')
            ->andWhere('event_type = :type')
            ->setParameter('aid', $recipient->getId())
            ->setParameter('oid', $message->getUuid()->toRfc4122())
            ->setParameter('type', 'click')
            ->fetchAssociative()
        ;

        self::assertIsArray($row, 'The click must be written to app_hit.');
        self::assertSame(0, (int) $row['suspicious']);
        self::assertSame('https://parti-renaissance.fr/a', $row['target_url']);
    }

    private function processOpenWithDetector(Uuid $campaign, Uuid $adherent, ?string $ip, ?string $ua): void
    {
        // Only the deploy-time egress list is overridden (a known Apple relay range); parser, resolver,
        // writer and DB are the real services fetched from the container.
        $processor = new EngagementProcessor(
            self::getContainer()->get(SesEngagementParser::class),
            self::getContainer()->get(SesEventTargetResolver::class),
            self::getContainer()->get(EmailAppHitWriter::class),
            new SesOpenReliabilityClassifier(new AppleEgressCidrProvider($this->egressFile)),
        );

        $processor->process($this->openPayload($campaign, $adherent, $ip, $ua));
        $this->manager->clear();
    }

    private function processWithRealWiring(array $payload): void
    {
        self::getContainer()->get(EngagementProcessor::class)->process($payload);
        $this->manager->clear();
    }

    /**
     * @return array<string, mixed>
     */
    private function openPayload(Uuid $campaign, Uuid $adherent, ?string $ip, ?string $ua): array
    {
        $open = ['timestamp' => '2026-07-02T14:00:05.000Z'];
        if (null !== $ip) {
            $open['ipAddress'] = $ip;
        }
        if (null !== $ua) {
            $open['userAgent'] = $ua;
        }

        return ['Message' => json_encode([
            'eventType' => 'Open',
            'mail' => [
                'timestamp' => '2026-07-02T14:00:00.000Z',
                'tags' => [
                    'campaign_uuid' => [$campaign->toRfc4122()],
                    'adherent_uuid' => [$adherent->toRfc4122()],
                ],
            ],
            'open' => $open,
        ])];
    }

    /**
     * @return array<string, mixed>
     */
    private function clickPayload(Uuid $campaign, Uuid $adherent, string $url): array
    {
        return ['Message' => json_encode([
            'eventType' => 'Click',
            'mail' => ['tags' => [
                'campaign_uuid' => [$campaign->toRfc4122()],
                'adherent_uuid' => [$adherent->toRfc4122()],
            ]],
            'click' => ['timestamp' => '2026-07-02T14:00:05.000Z', 'link' => $url],
        ])];
    }

    /**
     * @return array<string, mixed>
     */
    private function reloadOpenHit(int $adherentId, string $objectId): array
    {
        $row = $this->manager->getConnection()->createQueryBuilder()
            ->select('suspicious', 'raw')
            ->from('app_hit')
            ->where('adherent_id = :aid')
            ->andWhere('object_id = :oid')
            ->andWhere('event_type = :type')
            ->setParameter('aid', $adherentId)
            ->setParameter('oid', $objectId)
            ->setParameter('type', 'open')
            ->fetchAssociative()
        ;

        self::assertIsArray($row, 'The open must be written to app_hit.');

        return $row;
    }

    /**
     * @return array{AdherentMessage, Adherent}
     */
    private function createSentCampaign(): array
    {
        $author = $this->persistAdherent();
        $recipient = $this->persistAdherent();

        // The target resolver only needs a persisted adherent and a sent message (no Mailchimp campaign).
        $message = new AdherentMessage(null, $author);
        $message->setSubject('Lettre de campagne');
        $message->setContent('<p>Bonjour</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);
        $message->markAsSent();

        $this->manager->persist($message);
        $this->manager->flush();

        return [$message, $recipient];
    }

    private function persistAdherent(): Adherent
    {
        $seq = ++$this->seq;
        $email = \sprintf('ses-open-%d@test.dev', $seq);

        $phone = new PhoneNumber();
        $phone->setCountryCode(33);
        $phone->setNationalNumber('140998211');

        $adherent = Adherent::create(
            Adherent::createUuid($email),
            \sprintf('SES-O-%d', $seq),
            $email,
            'super-password',
            'female',
            'Sesopen',
            'Martin',
            new \DateTime('1990-12-12'),
            ActivityPositionsEnum::STUDENT,
            $this->createPostAddress('92 bld du Général Leclerc', '92110-92024'),
            $phone,
            status: Adherent::ENABLED,
        );

        $this->manager->persist($adherent);
        $this->manager->flush();

        return $adherent;
    }
}
