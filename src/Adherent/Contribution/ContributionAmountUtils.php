<?php

namespace App\Adherent\Contribution;

class ContributionAmountUtils
{
    private const CONTRIBUTION_MIN_REVENUE_AMOUNT = 250;
    private const CONTRIBUTION_MAX_AMOUNT = 200;

    public static function needContribution(float $revenueAmount): bool
    {
        return $revenueAmount >= self::CONTRIBUTION_MIN_REVENUE_AMOUNT;
    }

    public static function getContributionAmount(float $revenueAmount): int
    {
        if (!static::needContribution($revenueAmount)) {
            return 0;
        }

        $contributionAmount = (int) round($revenueAmount * 2 / 100);

        if ($contributionAmount > self::CONTRIBUTION_MAX_AMOUNT) {
            return self::CONTRIBUTION_MAX_AMOUNT;
        }

        return $contributionAmount;
    }

    public static function getContributionAmountAfterTax(float $revenueAmount): int
    {
        return (int) round(static::getContributionAmount($revenueAmount) / 3);
    }
}
