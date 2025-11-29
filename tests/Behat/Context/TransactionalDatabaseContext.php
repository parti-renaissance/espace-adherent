<?php

declare(strict_types=1);

namespace Tests\App\Behat\Context;

use Behat\Behat\Context\Context;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;

class TransactionalDatabaseContext implements Context
{
    /**
     * @BeforeSuite
     */
    public static function enableStaticConnection(): void
    {
        StaticDriver::setKeepStaticConnections(true);
    }

    /**
     * @AfterSuite
     */
    public static function disableStaticConnection(): void
    {
        StaticDriver::setKeepStaticConnections(false);
    }

    /**
     * @BeforeScenario
     */
    public function beginTransaction(): void
    {
        StaticDriver::beginTransaction();
    }

    /**
     * @AfterScenario
     */
    public function rollBack(): void
    {
        StaticDriver::rollBack();
    }
}
