<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadPapCampaignData;
use App\DataFixtures\ORM\LoadPhoningCampaignData;
use App\Entity\Adherent;
use App\Entity\Pap\Campaign as PapCampaign;
use App\Entity\Phoning\Campaign as PhoningCampaign;
use App\Repository\AdherentRepository;
use App\Repository\DonationRepository;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * Smoke tests for repository queries that mix non-aggregated SELECT columns
 * with GROUP BY/DISTINCT. MySQL strict mode (ONLY_FULL_GROUP_BY) rejects these
 * unless the non-grouped columns are functionally dependent on the grouped PK,
 * and rejects ORDER BY columns not present in a DISTINCT SELECT list.
 *
 * These tests execute the queries against the real test database and assert no SQL
 * error is thrown. Result content is not asserted.
 */
#[Group('functional')]
class AdherentRepositoryStrictGroupByTest extends AbstractKernelTestCase
{
    public function testFindScoresByPhoningCampaignExecutes(): void
    {
        $campaign = $this->getEntityManager()
            ->getRepository(PhoningCampaign::class)
            ->findOneBy(['uuid' => LoadPhoningCampaignData::CAMPAIGN_1_UUID])
        ;
        self::assertNotNull($campaign, 'Fixture phoning campaign should exist');

        $result = $this->get(AdherentRepository::class)->findScoresByCampaign($campaign);
        self::assertIsArray($result);
    }

    public function testFindFullScoresByPhoningCampaignExecutes(): void
    {
        $campaign = $this->getEntityManager()
            ->getRepository(PhoningCampaign::class)
            ->findOneBy(['uuid' => LoadPhoningCampaignData::CAMPAIGN_1_UUID])
        ;
        self::assertNotNull($campaign, 'Fixture phoning campaign should exist');

        $result = $this->get(AdherentRepository::class)->findFullScoresByCampaign($campaign);
        self::assertIsArray($result);
    }

    public function testFindFullScoresByPapCampaignExecutes(): void
    {
        $campaign = $this->getEntityManager()
            ->getRepository(PapCampaign::class)
            ->findOneBy(['uuid' => LoadPapCampaignData::CAMPAIGN_1_UUID])
        ;
        self::assertNotNull($campaign, 'Fixture pap campaign should exist');

        $paginator = $this->get(AdherentRepository::class)->findFullScoresByPapCampaign($campaign);

        // Iterate to force query execution
        $rows = iterator_to_array($paginator);
        self::assertIsArray($rows);
    }

    public function testGetDonationYearsForAdherentExecutes(): void
    {
        $adherent = $this->getEntityManager()
            ->getRepository(Adherent::class)
            ->findOneBy(['emailAddress' => 'jacques.picard@en-marche.fr'])
        ;
        self::assertNotNull($adherent, 'Fixture adherent should exist');

        $result = $this->get(DonationRepository::class)->getDonationYearsForAdherent($adherent);
        self::assertIsArray($result);
    }
}
