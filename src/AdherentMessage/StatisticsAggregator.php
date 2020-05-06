<?php

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;

class StatisticsAggregator
{
    public function aggregateData(AdherentMessageInterface $message): array
    {
        $data = [
            'sent' => 0,
            'opens' => 0,
            'open_rate' => 0,
            'clicks' => 0,
            'click_rate' => 0,
            'unsubscribe' => 0,
            'unsubscribe_rate' => 0,
        ];

        foreach ($message->getMailchimpCampaigns() as $campaign) {
            if ($report = $campaign->getReport()) {
                $data['sent'] += $report->getEmailSent();
                $data['opens'] += $report->getOpenUnique();
                $data['clicks'] += $report->getClickUnique();
                $data['unsubscribe'] += $report->getUnsubscribed();
            }
        }

        if ($data['sent']) {
            $data['open_rate'] = self::calculateRate($data['opens'], $data['sent']);
            $data['click_rate'] = self::calculateRate($data['clicks'], $data['sent']);
            $data['unsubscribe_rate'] = self::calculateRate($data['unsubscribe'], $data['sent']);
        }

        return $data;
    }

    private static function calculateRate(int $part, int $total, int $precision = 1): float
    {
        return round($part * 100.00 / $total, $precision, \PHP_ROUND_HALF_UP);
    }
}
