<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

/**
 * Compares expected (local SQL) vs prepared (Mailchimp member_count) using the
 * empirically validated per-size thresholds (cf. DESIGN sections 5.5 and 2bis).
 *
 * Mailchimp silently filters out non-member / unsubscribed / cleaned emails.
 * The `member_count/expected` ratio naturally degrades with payload size,
 * hence the size-tiered thresholds.
 */
class AudienceCheckCalculator
{
    public function compute(int $expected, int $prepared): AudienceCheckEnum
    {
        if (0 === $expected) {
            return AudienceCheckEnum::Mismatch;
        }

        $ratio = abs($expected - $prepared) / $expected;

        [$matchThreshold, $driftThreshold] = match (true) {
            $expected <= 2_000 => [0.005, 0.02],   // 0.5% / 2%
            $expected <= 10_000 => [0.05, 0.08],   // 5% / 8%
            default => [0.10, 0.15],                // 10% / 15%
        };

        return match (true) {
            $ratio <= $matchThreshold => AudienceCheckEnum::Match,
            $ratio <= $driftThreshold => AudienceCheckEnum::Drift,
            default => AudienceCheckEnum::Mismatch,
        };
    }
}
