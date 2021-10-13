<?php

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Persistence\ManagerRegistry;

class FixtureContext extends RawMinkContext
{
    use KernelDictionary;

    private SymfonyFixturesLoader $fixturesLoader;
    private ORMPurger $purger;
    private ORMExecutor $executor;
    private ORMPurger $pgsqlPurger;
    private ORMExecutor $pgsqlExecutor;

    public function __construct(SymfonyFixturesLoader $fixturesLoader, ManagerRegistry $managerRegistry)
    {
        $this->fixturesLoader = $fixturesLoader;

        $defaultManager = $managerRegistry->getManager('default');
        $pgsqlManager = $managerRegistry->getManager('pgsql');

        $this->executor = new ORMExecutor($defaultManager, $this->purger = new ORMPurger($defaultManager));
        $this->pgsqlExecutor = new ORMExecutor($pgsqlManager, $this->pgsqlPurger = new ORMPurger($pgsqlManager));
    }

    /**
     * @BeforeScenario
     */
    public function clearDatabase()
    {
        $this->purger->purge();
        $this->pgsqlPurger->purge();
    }

    /**
     * @Given the following fixtures are loaded:
     */
    public function theFollowingFixturesAreLoaded(TableNode $classnames): void
    {
        $fixtures = [];

        foreach ($classnames->getRows() as $classname) {
            $this->loadFixture('App\\DataFixtures\\ORM\\'.$classname[0], $fixtures);
        }

        if (!$fixtures) {
            throw new InvalidArgumentException(sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $fixtures)));
        }

        $this->executor->execute($fixtures);
    }

    /**
     * @Given the following Pgsql fixtures are loaded:
     */
    public function theFollowingPgsqlFixturesAreLoaded(TableNode $classnames): void
    {
        $fixtures = [];

        foreach ($classnames->getRows() as $classname) {
            $this->loadFixture('App\\DataFixturesPgsql\\'.$classname[0], $fixtures);
        }

        if (!$fixtures) {
            throw new InvalidArgumentException(sprintf('Could not find any Pgsql fixtures to load in: %s', "\n\n- ".implode("\n- ", $fixtures)));
        }

        $this->pgsqlExecutor->execute($fixtures);
    }

    private function loadFixture(string $className, array &$fixtures): void
    {
        if (isset($fixtures[$className])) {
            return;
        }

        $fixture = $this->fixturesLoader->getFixture($className);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $depClassName) {
                $this->loadFixture($depClassName, $fixtures);
            }
        }

        $fixtures[$className] = $fixture;
    }
}
