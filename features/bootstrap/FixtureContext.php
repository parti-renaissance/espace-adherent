<?php

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;

class FixtureContext extends RawMinkContext
{
    use KernelDictionary;

    private $executor;

    public function __construct(EntityManager $manager)
    {
        $this->executor = new ORMExecutor($manager, new ORMPurger($manager));
    }

    /**
     * @Given the following fixtures are loaded:
     */
    public function theFollowingFixturesAreLoaded(TableNode $classnames): void
    {
        $path = __DIR__.'/../../src/DataFixtures/ORM';
        $loader = new ContainerAwareLoader($this->getContainer());

        foreach ($classnames->getRows() as $classname) {
            $loader->loadFromFile($path.'/'.$classname[0].'.php');
        }

        $fixtures = $loader->getFixtures();
        if (!$fixtures) {
            throw new InvalidArgumentException(
                sprintf('Could not find any fixtures to load in: %s', "\n\n- ".implode("\n- ", $fixtures))
            );
        }

        $this->executor->execute($fixtures);
    }
}
