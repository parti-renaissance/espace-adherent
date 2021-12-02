<?php

namespace Tests\App\JeMarche;

use App\JeMarche\NotificationTopicBuilder;
use App\Repository\AdherentRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class NotificationTopicBuilderTest extends AbstractKernelTestCase
{
    private ?AdherentRepository $adherentRepository = null;
    private ?NotificationTopicBuilder $notificationTopicBuilder = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->notificationTopicBuilder = $this->get(NotificationTopicBuilder::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->adherentRepository = null;
        $this->notificationTopicBuilder = null;
    }

    /**
     * @dataProvider provideAdherentTopics
     */
    public function testGetTopicsFromAdherent(string $email, array $expectedTopics): void
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);

        $topics = $this->notificationTopicBuilder->getTopicsFromAdherent($adherent);

        self::assertSame($expectedTopics, $topics);
    }

    public function provideAdherentTopics(): iterable
    {
        yield ['francis.brioul@yahoo.com', [
            'staging_jemarche_department_77',
            'staging_jemarche_region_11',
            'staging_jemarche_global',
        ]];

        yield ['carl999@example.fr', [
            'staging_jemarche_department_73',
            'staging_jemarche_region_84',
            'staging_jemarche_global',
        ]];

        yield ['jacques.picard@en-marche.fr', [
            'staging_jemarche_borough_75008',
            'staging_jemarche_department_75',
            'staging_jemarche_region_11',
            'staging_jemarche_global',
        ]];
    }
}
