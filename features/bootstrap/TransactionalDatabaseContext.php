<?php

use Behat\Behat\Context\Context;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;

class TransactionalDatabaseContext implements Context
{
    /**
     * @BeforeSuite
     */
    public function enableStaticConnection(): void
    {
        StaticDriver::setKeepStaticConnections(true);
    }

    /**
     * @AfterSuite
     */
    public function disableStaticConnection(): void
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
