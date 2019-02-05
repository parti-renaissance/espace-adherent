<?php

use AppBundle\Entity\RepublicanSilence;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use Doctrine\ORM\EntityManagerInterface;

class FixtureContext extends RawMinkContext
{
    use KernelDictionary;

    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @BeforeSuite
     */
    public static function beforeSuite()
    {
        StaticDriver::setKeepStaticConnections(true);
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        StaticDriver::beginTransaction();
    }

    /**
     * @AfterScenario
     */
    public function afterScenario()
    {
        StaticDriver::rollBack();
    }

    /**
     * @AfterSuite
     */
    public static function afterSuite()
    {
        StaticDriver::setKeepStaticConnections(false);
    }

    /**
     * @Given the republican silence is disabled
     */
    public function theRpublicanSilenceIsDisabled(): void
    {
        $this
            ->getEntityManager()
            ->getRepository(RepublicanSilence::class)
            ->createQueryBuilder('r')
            ->delete()
            ->getQuery()
            ->execute()
        ;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
