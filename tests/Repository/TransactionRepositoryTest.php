<?php

namespace Tests\AppBundle\Repository;

use AppBundle\DataFixtures\ORM\LoadDonationData;
use AppBundle\Repository\TransactionRepository;
use Cake\Chronos\Chronos;
use Tests\AppBundle\MysqlWebTestCase;
use Tests\AppBundle\TestHelperTrait;

class TransactionRepositoryTest extends MysqlWebTestCase
{
    use TestHelperTrait;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    public function testGetTotalAmountCurrentYearByEmail(): void
    {
        Chronos::setTestNow(Chronos::createFromFormat('Y/m/d H:i:s', '2018/06/15 15:16:17'));

        static::assertSame(25000, $this->transactionRepository->getTotalAmountInCentsByEmail('jacques.picard@en-marche.fr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadDonationData::class,
        ]);

        $this->container = $this->getContainer();
        $this->transactionRepository = $this->getTransactionRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->transactionRepository = null;
        $this->container = null;

        parent::tearDown();
    }
}
