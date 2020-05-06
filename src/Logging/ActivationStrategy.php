<?php

namespace App\Logging;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ActivationStrategy extends ErrorLevelActivationStrategy
{
    private $ignoredStatusCodes;

    public function __construct(int $actionLevel, array $ignoredStatusCodes)
    {
        parent::__construct($actionLevel);

        $this->ignoredStatusCodes = $ignoredStatusCodes;
    }

    public function isHandlerActivated(array $record)
    {
        return parent::isHandlerActivated($record) && $this->isRecordActivated($record);
    }

    private function isRecordActivated($record): bool
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            /** @var \Exception $exception */
            $exception = $record['context']['exception'];

            // Find the root usable exception
            while ($exception->getPrevious() && !$exception instanceof HttpExceptionInterface && !$exception instanceof AccessDeniedException) {
                $exception = $exception->getPrevious();
            }

            if ($exception instanceof HttpExceptionInterface && \in_array($exception->getStatusCode(), $this->ignoredStatusCodes)) {
                return false;
            } elseif ($exception instanceof AccessDeniedException && \in_array($exception->getCode(), $this->ignoredStatusCodes)) {
                return false;
            }
        }

        return true;
    }
}
