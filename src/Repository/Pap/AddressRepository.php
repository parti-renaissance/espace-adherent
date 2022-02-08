<?php

namespace App\Repository\Pap;

use App\Entity\Pap\Address;
use App\Entity\Pap\Campaign;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

class AddressRepository extends ServiceEntityRepository
{
    use GeoZoneTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Address::class);
    }

    /** @return Address[] */
    public function findNear(
        array $activeCampaigns,
        float $latitude,
        float $longitude,
        ?float $latitudeDelta,
        ?float $longitudeDelta,
        int $limit = 300
    ): array {
        $correction = $longitudeDelta ? 0 : 32;
        $latitudeDelta = $latitudeDelta ? $latitudeDelta / 2 : 0;
        $longitudeDelta = $longitudeDelta ? $longitudeDelta / 2 : 0;

        $activeCampaignsCondition = implode(', ', array_map(function (int $key) {
            return ':active_campaign_'.$key;
        }, array_keys($activeCampaigns)));

        $sql = <<<SQL
SELECT address.id
FROM pap_address AS address
INNER JOIN pap_building AS building ON building.address_id = address.id AND building.current_campaign_id IN ($activeCampaignsCondition)
WHERE 
    address.offset_x BETWEEN :offset_x_1 AND :offset_x_2
    AND address.offset_y BETWEEN :offset_y_1 AND :offset_y_2
ORDER BY 
    (6371 * 
         ACOS(
           COS(RADIANS(:latitude)) 
         * COS(RADIANS(address.latitude)) 
         * COS(RADIANS(address.longitude) - RADIANS(:longitude)) 
         + SIN(RADIANS(:latitude)) 
         * SIN(RADIANS(address.latitude))
     ))
LIMIT :limit
SQL;
        $offsetX1 = floor(($longitude + 180 - $longitudeDelta) / 360 * 131072) - $correction;
        $offsetX2 = floor(($longitude + 180 + $longitudeDelta) / 360 * 131072) + $correction;
        $offsetY1 = floor((1.0 - log(tan(deg2rad($latitude + $latitudeDelta)) + 1.0 / cos(deg2rad($latitude + $latitudeDelta))) / pi()) / 2.0 * 131072) - $correction;
        $offsetY2 = floor((1.0 - log(tan(deg2rad($latitude - $latitudeDelta)) + 1.0 / cos(deg2rad($latitude - $latitudeDelta))) / pi()) / 2.0 * 131072) + $correction;

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);

        $stmt->bindValue('latitude', $latitude);
        $stmt->bindValue('longitude', $longitude);
        $stmt->bindValue('offset_x_1', $offsetX1);
        $stmt->bindValue('offset_x_2', $offsetX2);
        $stmt->bindValue('offset_y_1', $offsetY1);
        $stmt->bindValue('offset_y_2', $offsetY2);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

        foreach ($activeCampaigns as $key => $value) {
            $stmt->bindValue('active_campaign_'.$key, $value, \PDO::PARAM_INT);
        }

        $result = $stmt->executeQuery();

        return $this
            ->createQueryBuilder('address')
            ->select('address', 'building', 'stats')
            ->addSelect('
                (6371 * 
                ACOS(
                    COS(RADIANS(:latitude)) 
                    * COS(RADIANS(address.latitude)) 
                    * COS(RADIANS(address.longitude) - RADIANS(:longitude)) 
                    + SIN(RADIANS(:latitude)) 
                    * SIN(RADIANS(address.latitude))
                )) as HIDDEN distance
            ')
            ->innerJoin('address.building', 'building')
            ->leftJoin('building.statistics', 'stats', Join::WITH, 'stats.campaign = building.currentCampaign')
            ->where('address.id IN (:address_ids)')
            ->setParameters([
                'address_ids' => array_keys($result->fetchAllAssociativeIndexed()),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ])
            ->orderBy('distance', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function associatedCampaign(Campaign $campaign): void
    {
        $sql = <<<SQL
UPDATE pap_address AS address
INNER JOIN pap_vote_place AS vote_place ON vote_place.id = address.vote_place_id
INNER JOIN pap_building AS building ON building.address_id = address.id
LEFT JOIN pap_campaign AS current_campaign ON current_campaign.id = building.current_campaign_id 
SET building.current_campaign_id = :campaign_id
WHERE current_campaign.id IS NULL OR current_campaign.finish_at < :start_date
SQL;
        $conditions = [];

        $params = [
            'campaign_id' => $campaign->getId(),
            'start_date' => $campaign->getBeginAt()->format('Y-m-d H:i:s'),
        ];

        if (null !== $campaign->getDeltaPredictionAndResultMin2017()) {
            $conditions[] = 'vote_place.delta_prediction_and_result_2017 >= :delta_prediction_and_result_min_2017';
            $params['delta_prediction_and_result_min_2017'] = $campaign->getDeltaPredictionAndResultMin2017();
        }

        if (null !== $campaign->getDeltaPredictionAndResultMax2017()) {
            $conditions[] = 'vote_place.delta_prediction_and_result_2017 <= :delta_prediction_and_result_max_2017';
            $params['delta_prediction_and_result_max_2017'] = $campaign->getDeltaPredictionAndResultMax2017();
        }

        if (null !== $campaign->getDeltaAveragePredictionsMin()) {
            $conditions[] = 'vote_place.delta_average_predictions >= :delta_average_predictions_min';
            $params['delta_average_predictions_min'] = $campaign->getDeltaAveragePredictionsMin();
        }

        if (null !== $campaign->getDeltaAveragePredictionsMax()) {
            $conditions[] = 'vote_place.delta_average_predictions <= :delta_average_predictions_max';
            $params['delta_average_predictions_max'] = $campaign->getDeltaAveragePredictionsMax();
        }

        if (null !== $campaign->getAbstentionsMin2017()) {
            $conditions[] = 'vote_place.abstentions_2017 >= :abstentions_min_2017';
            $params['abstentions_min_2017'] = $campaign->getAbstentionsMin2017();
        }

        if (null !== $campaign->getAbstentionsMax2017()) {
            $conditions[] = 'vote_place.abstentions_2017 <= :abstentions_max_2017';
            $params['abstentions_max_2017'] = $campaign->getAbstentionsMax2017();
        }

        if (null !== $campaign->getMisregistrationsPriorityMin()) {
            $conditions[] = 'vote_place.misregistrations_priority >= :misregistrations_priority_min';
            $params['misregistrations_priority_min'] = $campaign->getMisregistrationsPriorityMin();
        }

        if (null !== $campaign->getMisregistrationsPriorityMax()) {
            $conditions[] = 'vote_place.misregistrations_priority <= :misregistrations_priority_max';
            $params['misregistrations_priority_max'] = $campaign->getMisregistrationsPriorityMax();
        }

        if ($conditions) {
            $sql .= ' AND '.implode(' AND ', $conditions);
        }

        $statement = $this->getEntityManager()->getConnection()->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->executeStatement();
    }

    public function countByPapCampaign(Campaign $campaign): int
    {
        if ($campaign->isNationalVisibility()) {
            return (int) $this->createQueryBuilder('address')
                ->select('COUNT(1)')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        $qb = $this->createQueryBuilder('address');
        $qb = $this->withGeoZones(
            [$campaign->getZone()],
            $qb,
            'address',
            Address::class,
            'a2',
            'zones',
            'z2'
        );

        return (int) $qb
            ->select('COUNT(DISTINCT address.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function countVotersByPapCampaign(Campaign $campaign): int
    {
        if ($campaign->isNationalVisibility()) {
            return (int) $this->createQueryBuilder('address')
                ->select('SUM(address.votersCount)')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        $qb = $this->createQueryBuilder('address');
        $qb = $this->withGeoZones(
            [$campaign->getZone()],
            $qb,
            'address',
            Address::class,
            'a2',
            'zones',
            'z2'
        );

        return (int) $qb
            ->select('SUM(address.votersCount)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
