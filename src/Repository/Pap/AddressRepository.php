<?php

declare(strict_types=1);

namespace App\Repository\Pap;

use App\Entity\Pap\Address;
use App\Entity\Pap\Campaign;
use App\Entity\Pap\VotePlace;
use App\Repository\GeoZoneTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository<\App\Entity\Pap\Address>
 */
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
        int $limit = 300,
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
        $offsetY1 = floor((1.0 - log(tan(deg2rad($latitude + $latitudeDelta)) + 1.0 / cos(deg2rad($latitude + $latitudeDelta))) / \M_PI) / 2.0 * 131072) - $correction;
        $offsetY2 = floor((1.0 - log(tan(deg2rad($latitude - $latitudeDelta)) + 1.0 / cos(deg2rad($latitude - $latitudeDelta))) / \M_PI) / 2.0 * 131072) + $correction;

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
            ->setParameters(new ArrayCollection([new Parameter('address_ids', array_keys($result->fetchAllAssociativeIndexed())), new Parameter('latitude', $latitude), new Parameter('longitude', $longitude)]))
            ->orderBy('distance', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function associatedCampaign(Campaign $campaign): void
    {
        $sql = <<<SQL
            UPDATE pap_address AS address
            INNER JOIN pap_building AS building ON building.address_id = address.id
            LEFT JOIN pap_campaign AS current_campaign ON current_campaign.id = building.current_campaign_id
            SET building.current_campaign_id = :campaign_id
            WHERE (current_campaign.id IS NULL OR current_campaign.finish_at < :start_date)
            __VOTE_PLACE_CONDITION__
            SQL;
        $conditions = [];

        $params = [
            'campaign_id' => $campaign->getId(),
            'start_date' => $campaign->getBeginAt()->format('Y-m-d H:i:s'),
        ];

        if (!$campaign->isNationalVisibility()) {
            $votePlaceIds = implode(',', array_map(function (VotePlace $votePlace) {
                return $votePlace->getId();
            }, $campaign->getVotePlaces()->toArray()));

            if (!$votePlaceIds) {
                return;
            }

            $conditions[] = "vote_place.id IN ($votePlaceIds)";
        }

        $sql = str_replace(
            '__VOTE_PLACE_CONDITION__',
            $conditions ?
                \sprintf(
                    'AND address.vote_place_id IN (SELECT vote_place.id FROM pap_vote_place AS vote_place WHERE %s)',
                    implode(' AND ', $conditions)
                ) : '',
            $sql
        );

        $connection = $this->getEntityManager()->getConnection();
        $connection->prepare($sql)->executeStatement($params);

        // unlink campaign from buildings that not in campaign's vote places
        if (isset($votePlaceIds)) {
            $connection->prepare(<<<SQL
                UPDATE pap_address AS address
                INNER JOIN pap_building AS building ON building.address_id = address.id
                SET building.current_campaign_id = NULL
                WHERE building.current_campaign_id = :campaign_id
                  AND address.vote_place_id NOT IN ($votePlaceIds)
                SQL)->executeStatement([
                'campaign_id' => $campaign->getId(),
            ]);
        }
    }

    public function countByPapCampaign(Campaign $campaign): ?array
    {
        $qb = $this
            ->createQueryBuilder('address')
            ->select(
                'COUNT(1) AS total_addresses',
                'SUM(address.votersCount) AS total_voters',
            )
        ;

        if ($campaign->isNationalVisibility()) {
            return $qb->getQuery()->getSingleResult();
        }

        if ($campaign->getVotePlaces()->isEmpty()) {
            return null;
        }

        return $qb
            ->leftJoin('address.votePlace', 'vote_place')
            ->where('vote_place IN (:vote_places)')
            ->setParameter('vote_places', $campaign->getVotePlaces()->toArray())
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
