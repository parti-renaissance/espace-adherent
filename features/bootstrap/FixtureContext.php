<?php

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\Bundle\FixturesBundle\Loader\SymfonyFixturesLoader;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;

class FixtureContext extends RawMinkContext
{
    use KernelDictionary;

    private $executor;
    private $purger;
    private $fixturesLoader;

    public function __construct(SymfonyFixturesLoader $fixturesLoader, EntityManager $manager)
    {
        $this->fixturesLoader = $fixturesLoader;
        $this->executor = new ORMExecutor($manager, $this->purger = new ORMPurger($manager));
    }

    /**
     * @BeforeScenario
     */
    public function clearDatabase()
    {
        $this->purger->purge();
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
