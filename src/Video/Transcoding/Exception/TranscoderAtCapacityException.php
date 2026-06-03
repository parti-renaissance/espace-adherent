<?php

declare(strict_types=1);

namespace App\Video\Transcoding\Exception;

/**
 * Signals that a transcoding job must not be created right now because the GCP concurrent-job quota is
 * (about to be) reached. Thrown by the launcher both proactively (cheap DB count gate) and reactively
 * (GCP returned RESOURCE_EXHAUSTED). The carried cause and active job count let callers log precisely
 * and decide whether to defer the work or give up.
 */
class TranscoderAtCapacityException extends \RuntimeException
{
    public const string CAUSE_PROACTIVE = 'proactive';
    public const string CAUSE_REACTIVE = 'reactive';

    private function __construct(
        string $message,
        public readonly string $cause,
        public readonly ?int $activeJobCount = null,
    ) {
        parent::__construct($message);
    }

    public static function proactive(int $activeJobCount, int $threshold): self
    {
        return new self(
            \sprintf('Transcoder at capacity: %d active job(s) >= threshold %d.', $activeJobCount, $threshold),
            self::CAUSE_PROACTIVE,
            $activeJobCount,
        );
    }

    public static function reactive(): self
    {
        return new self('Transcoder rejected the job with RESOURCE_EXHAUSTED.', self::CAUSE_REACTIVE);
    }
}
