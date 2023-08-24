<?php

namespace Tests\App\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\ContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Hook\BeforeScenario;
use Behat\MinkExtension\Context\RawMinkContext;

class AppContext extends RawMinkContext
{
    private const TAG_RENAISSANCE = 'renaissance';
    private const TAG_RENAISSANCE_API = 'renaissance_api';
    private const TAG_RENAISSANCE_APP = 'renaissance_app';

    private const RENAISSANCE_TAGS = [
        self::TAG_RENAISSANCE,
        self::TAG_RENAISSANCE_API,
        self::TAG_RENAISSANCE_APP,
    ];

    #[BeforeScenario]
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $tags = $scope->getFeature()->getTags();

        if ($renaissanceTags = array_intersect($tags, self::RENAISSANCE_TAGS)) {
            $renaissanceTag = current($renaissanceTags);

            /** @var ContextEnvironment $env */
            $env = $scope->getEnvironment();
            /** @var DIContext $diContext */
            $diContext = $env->getContext(DIContext::class);

            $baseUrlPrefix = match ($renaissanceTag) {
                self::TAG_RENAISSANCE_API => 'api.',
                self::TAG_RENAISSANCE_APP => 'app.',
                default => '',
            };
            $baseUrl = $baseUrlPrefix.$diContext->getParameter('renaissance_host');

            /** @var Context[] $contexts */
            $contexts = array_map(fn (string $contextClass) => $env->getContext($contextClass), $env->getContextClasses());

            array_map(
                fn (RawMinkContext $context) => $context->setMinkParameter('base_url', 'http://'.$baseUrl),
                array_filter($contexts, fn (Context $context) => $context instanceof RawMinkContext)
            );
        }
    }
}
