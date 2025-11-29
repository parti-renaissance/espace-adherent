<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use Doctrine\Common\Collections\Collection;

interface CampaignStatisticsOwnerInterface
{
    public function getId(): ?int;

    public function getStatistics(): Collection;

    public function addStatistic(CampaignStatisticsInterface $statistic): void;

    public function removeStatistic(CampaignStatisticsInterface $statistic): void;

    public function findStatisticsForCampaign(Campaign $campaign): ?CampaignStatisticsInterface;
}
