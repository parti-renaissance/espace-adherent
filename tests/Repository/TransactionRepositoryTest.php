<?php

namespace Tests\AppBundle\Repository;

use AppBundle\Entity\Transaction;
use AppBundle\Repository\TransactionRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

class TransactionRepositoryTest extends WebTestCase
{
    use ControllerTestTrait;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    public function testGetTotalAmountCurrentYearByEmail(): void
    {
        // Update all transactions to start of this year
        // so we can test by counting the amount of all transactions
        $this
            ->manager
            ->createQueryBuilder()
            ->update(Transaction::class, 't')
            ->set('t.payboxDateTime', ':donatedAt')
            ->setParameter('donatedAt', new \DateTime('first day of january this year'), 'datetime')
            ->getQuery()
            ->execute()
        ;

        static::assertSame(25000, $this->transactionRepository->getTotalAmountInCentsByEmail('jacques.picard@en-marche.fr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->transactionRepository = $this->getTransactionRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->transactionRepository = null;

        parent::tearDown();
    }
}
