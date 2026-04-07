<?php

declare(strict_types=1);

namespace App\Firebase;

use App\Repository\PushTokenRepository;
use Kreait\Firebase\Messaging\MulticastSendReport;

class PushTokenStatusManager
{
    public function __construct(private readonly PushTokenRepository $pushTokenRepository)
    {
    }

    public function processReport(MulticastSendReport $report): void
    {
        $unknownTokens = $report->unknownTokens();

        if (!empty($unknownTokens)) {
            $this->pushTokenRepository->markAsUnsubscribed($unknownTokens, PushTokenUnsubscribeReasonEnum::TOKEN_UNKNOWN);
        }

        $invalidTokens = $report->invalidTokens();

        if (!empty($invalidTokens)) {
            $this->pushTokenRepository->markAsUnsubscribed($invalidTokens, PushTokenUnsubscribeReasonEnum::TOKEN_INVALID);
        }

        $successTokens = $report->validTokens();

        if (!empty($successTokens)) {
            $this->pushTokenRepository->updateLastNotificationAt($successTokens);
        }
    }
}
