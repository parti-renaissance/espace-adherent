<?php

declare(strict_types=1);

namespace App\Entity\Pap;

use Doctrine\Common\Collections\Collection;

trait CampaignStatisticsTrait
{
    /**
     * @return CampaignStatisticsInterface[]|Collection
     */
    public function getStatistics(): Collection
    {
        return $this->statistics;
    }

    public function addStatistic(CampaignStatisticsInterface $statistic): void
    {
        if (!$this->statistics->contains($statistic)) {
            $this->statistics->add($statistic);
        }
    }

    public function removeStatistic(CampaignStatisticsInterface $statistic): void
    {
        $this->statistics->removeElement($statistic);
    }

    public function findStatisticsForCampaign(Campaign $campaign): ?CampaignStatisticsInterface
    {
        foreach ($this->statistics as $statistic) {
            if ($statistic->getCampaign()->getId() === $campaign->getId()) {
                return $statistic;
            }
        }

        return null;
    }
}
