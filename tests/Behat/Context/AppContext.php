<?php

namespace Tests\App\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Hook\BeforeScenario;
use Behat\MinkExtension\Context\RawMinkContext;

class AppContext extends RawMinkContext
{
    #[BeforeScenario]
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        if ($scope->getFeature()->hasTag('renaissance')) {
            $env = $scope->getEnvironment();
            /** @var DIContext $diContext */
            $diContext = $env->getContext(DIContext::class);

            $contexts = array_map(fn (string $contextClass) => $env->getContext($contextClass), $env->getContextClasses());

            array_map(
                fn (RawMinkContext $context) => $context->setMinkParameter('base_url', 'http://'.$diContext->getParameter('renaissance_host')),
                array_filter($contexts, fn (Context $context) => $context instanceof RawMinkContext)
            );
        }
    }
}
