<?php

namespace Tests\App\Behat\Context;

use App\Entity\RepublicanSilence;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Hook\BeforeScenario;
use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\ORM\EntityManagerInterface;

class RepublicanSilenceContext extends RawMinkContext
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[BeforeScenario]
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $tags = $scope->getFeature()->getTags();

        if (!\in_array('republican_silence', $tags)) {
            $this
                ->entityManager->getRepository(RepublicanSilence::class)
                ->createQueryBuilder('r')
                ->delete()
                ->getQuery()
                ->execute()
            ;
        }
    }
}
