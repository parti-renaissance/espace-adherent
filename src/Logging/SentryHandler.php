<?php

namespace App\Logging;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\RavenHandler;
use Monolog\Logger;
use Raven_Client;

class SentryHandler extends RavenHandler
{
    private $levels = [
        Logger::DEBUG => Raven_Client::DEBUG,
        Logger::INFO => Raven_Client::INFO,
        Logger::NOTICE => Raven_Client::INFO,
        Logger::WARNING => Raven_Client::WARNING,
        Logger::ERROR => Raven_Client::ERROR,
        Logger::CRITICAL => Raven_Client::FATAL,
        Logger::ALERT => Raven_Client::FATAL,
        Logger::EMERGENCY => Raven_Client::FATAL,
    ];

    /**
     * {@inheritdoc}
     */
    public function handleBatch(array $records)
    {
        $level = $this->level;

        // Filter records based on their level
        $records = array_filter($records, function ($record) use ($level) {
            return $record['level'] >= $level;
        });

        if (!$records) {
            return;
        }

        // The record with the highest severity is the "main" one
        $main = array_reduce($records, function ($highest, $record) {
            if ($record['level'] > $highest['level']) {
                return $record;
            }

            return $highest;
        });

        $formatter = $this->getBatchFormatter();

        foreach ($records as $record) {
            $breadcrumb = [
                'message' => $record['message'],
                'category' => $record['channel'],
                'level' => $this->levels[$record['level']],
            ];

            if ($record !== $main) {
                $record = $formatter->format($this->processRecord($record));

                if ($record['context']) {
                    foreach ($record['context'] as $key => $value) {
                        $breadcrumb['data'][$key] = is_scalar($value) ? $value : json_encode($value, \JSON_UNESCAPED_SLASHES);
                    }
                }
            }

            $this->ravenClient->breadcrumbs->record($breadcrumb);
        }

        $this->handle($this->processRecord($main));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultBatchFormatter()
    {
        return new NormalizerFormatter();
    }
}
