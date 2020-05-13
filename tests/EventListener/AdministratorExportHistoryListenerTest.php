<?php

namespace Tests\App\Repository;

use App\Entity\Reporting\AdministratorExportHistory;
use Doctrine\ORM\EntityRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class AdministratorExportHistoryListenerTest extends WebTestCase
{
    /**
     * @var EntityRepository
     */
    private $historyRepository;

    use ControllerTestTrait;

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->historyRepository = $this->getRepository(AdministratorExportHistory::class);
    }

    protected function tearDown()
    {
        $this->kill();

        $this->historyRepository = null;

        parent::tearDown();
    }

    public function testCreateHistory()
    {
        self::assertEmpty($this->historyRepository->findAll());

        $this->authenticateAsAdmin($this->client, 'admin@en-marche-dev.fr');

        $parameters = [
            'filter' => [
                'city' => [
                    'value' => 'Bordeaux',
                ],
            ],
            'format' => 'csv',
        ];

        $this->client->request('GET', '/admin/app/adherent/export', $parameters);

        $histories = $this->historyRepository->findAll();
        self::assertCount(1, $histories);

        /** @var AdministratorExportHistory $history */
        $history = reset($histories);
        self::assertSame('admin@en-marche-dev.fr', $history->getAdministrator()->getEmailAddress());
        self::assertSame('admin_app_adherent_export', $history->getRouteName());
        self::assertSame($parameters, $history->getParameters());
    }
}
