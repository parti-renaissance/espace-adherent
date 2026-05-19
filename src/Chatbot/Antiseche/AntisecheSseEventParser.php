<?php

declare(strict_types=1);

namespace App\Chatbot\Antiseche;

final class AntisecheSseEventParser
{
    private const EVENT_DELIMITER = "\n\n";
    private const EVENT_PREFIX = 'event: ';
    private const DATA_PREFIX = 'data: ';

    private string $buffer = '';

    public function append(string $chunk): void
    {
        $this->buffer .= $chunk;
    }

    /** @return iterable<array{event: string, data: array<string, mixed>}> */
    public function drainEvents(): iterable
    {
        while (false !== ($pos = strpos($this->buffer, self::EVENT_DELIMITER))) {
            $block = substr($this->buffer, 0, $pos);
            $this->buffer = substr($this->buffer, $pos + \strlen(self::EVENT_DELIMITER));

            $parsed = $this->parseBlock($block);
            if (null !== $parsed) {
                yield $parsed;
            }
        }
    }

    /** @return array{event: string, data: array<string, mixed>}|null */
    private function parseBlock(string $block): ?array
    {
        $event = null;
        $data = null;
        foreach (explode("\n", $block) as $line) {
            if (str_starts_with($line, self::EVENT_PREFIX)) {
                $event = substr($line, \strlen(self::EVENT_PREFIX));
            } elseif (str_starts_with($line, self::DATA_PREFIX)) {
                $data = substr($line, \strlen(self::DATA_PREFIX));
            }
        }

        if (null === $event || null === $data) {
            return null;
        }

        try {
            $decoded = json_decode($data, true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return ['event' => $event, 'data' => \is_array($decoded) ? $decoded : []];
    }
}
