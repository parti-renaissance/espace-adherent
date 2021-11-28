<?php

namespace App\Entity\Pap;

use Doctrine\Common\Collections\Collection;

interface CampaignStatisticsOwnerInterface
{
    public function getStatistics(): Collection;

    public function addStatistic(CampaignStatisticsInterface $statistic): void;

    public function removeStatistic(CampaignStatisticsInterface $statistic): void;

    public function findStatisticsForCampaign(Campaign $campaign): ?CampaignStatisticsInterface;
}
