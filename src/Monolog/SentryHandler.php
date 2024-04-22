<?php

namespace App\Monolog;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

use function Sentry\addBreadcrumb;

use Sentry\Breadcrumb;
use Sentry\Monolog\Handler;
use Sentry\State\Scope;

use function Sentry\withScope;

class SentryHandler implements HandlerInterface
{
    private array $levels = [
        Logger::DEBUG => Breadcrumb::LEVEL_DEBUG,
        Logger::INFO => Breadcrumb::LEVEL_INFO,
        Logger::NOTICE => Breadcrumb::LEVEL_INFO,
        Logger::WARNING => Breadcrumb::LEVEL_WARNING,
        Logger::ERROR => Breadcrumb::LEVEL_ERROR,
        Logger::CRITICAL => Breadcrumb::LEVEL_FATAL,
        Logger::ALERT => Breadcrumb::LEVEL_FATAL,
        Logger::EMERGENCY => Breadcrumb::LEVEL_FATAL,
    ];

    /** @var Handler|HandlerInterface */
    private HandlerInterface $decorated;

    public function __construct(HandlerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function handleBatch(array $records): void
    {
        if (!$records) {
            return;
        }

        // The record with the highest severity is the "main" one
        $main = array_reduce($records, function ($highest, $record) {
            if (null === $highest || $record['level'] > $highest['level']) {
                return $record;
            }

            return $highest;
        });

        foreach ($records as $record) {
            if ($record !== $main) {
                $breadcrumb = new Breadcrumb(
                    $this->levels[$record['level']],
                    Breadcrumb::TYPE_DEFAULT,
                    $record['channel'],
                    $record['message'],
                    $record['context'] ?? [],
                    $record['datetime']->getTimestamp()
                );

                addBreadcrumb($breadcrumb);
            }
        }

        $this->handle($main);
    }

    public function handle(array $record): bool
    {
        $result = false;

        withScope(function (Scope $scope) use (&$result, $record): void {
            $scope->setExtras($record['extra'] ?? []);

            foreach ($record['context'] ?? [] as $key => $value) {
                $scope->setContext($key, $value);
            }

            $result = $this->decorated->handle($record);
        });

        return $result;
    }

    public function isHandling(array $record): bool
    {
        return $this->decorated->isHandling($record);
    }

    public function close(): void
    {
        $this->decorated->close();
    }
}
