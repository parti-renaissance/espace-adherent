<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

class AppleEgressCidrProvider
{
    /** @var string[]|null */
    private ?array $cidrs = null;

    public function __construct(private readonly string $path)
    {
    }

    /**
     * @return string[]
     */
    public function getCidrs(): array
    {
        if (null !== $this->cidrs) {
            return $this->cidrs;
        }

        if ('' === $this->path || !is_file($this->path)) {
            return $this->cidrs = [];
        }

        $lines = file($this->path, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
        if (false === $lines) {
            return $this->cidrs = [];
        }

        return $this->cidrs = array_values(array_filter($lines, static function (string $line): bool {
            return '' !== $line && !str_starts_with($line, '#');
        }));
    }
}
