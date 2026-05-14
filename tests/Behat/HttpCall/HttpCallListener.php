<?php

declare(strict_types=1);

namespace Tests\App\Behat\HttpCall;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\Tester\Result\ExecutedStepResult;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Mink;
use Behatch\HttpCall\ContextSupportedVoter;
use Behatch\HttpCall\HttpCallResult;
use Behatch\HttpCall\HttpCallResultPool;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WebDriver\Exception;

class HttpCallListener implements EventSubscriberInterface
{
    private ContextSupportedVoter $contextSupportedVoter;
    private HttpCallResultPool $httpCallResultPool;
    private Mink $mink;

    public function __construct(ContextSupportedVoter $contextSupportedVoter, HttpCallResultPool $httpCallResultPool, Mink $mink)
    {
        $this->contextSupportedVoter = $contextSupportedVoter;
        $this->httpCallResultPool = $httpCallResultPool;
        $this->mink = $mink;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StepTested::AFTER => 'afterStep',
        ];
    }

    public function afterStep(AfterStepTested $event): void
    {
        $testResult = $event->getTestResult();

        if (!$testResult instanceof ExecutedStepResult) {
            return;
        }

        $httpCallResult = new HttpCallResult(
            $testResult->getCallResult()->getReturn()
        );

        if ($this->contextSupportedVoter->vote($httpCallResult)) {
            $this->httpCallResultPool->store($httpCallResult);

            return;
        }

        try {
            if ($this->mink->getSession()->isStarted()) {
                $this->httpCallResultPool->store(
                    new HttpCallResult($this->mink->getSession()->getPage()->getContent())
                );
            }
        } catch (\LogicException|DriverException|Exception $e) {
        }
    }
}
