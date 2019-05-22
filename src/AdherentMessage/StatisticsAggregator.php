<?php

namespace AppBundle\AdherentMessage;

use AppBundle\Entity\AdherentMessage\AdherentMessageInterface;

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
            $data['open_rate'] = $data['opens'] * 100.00 / $data['sent'];
            $data['click_rate'] = $data['clicks'] * 100.00 / $data['sent'];
            $data['unsubscribe_rate'] = $data['unsubscribe'] * 100.00 / $data['sent'];
        }

        return $data;
    }
}
