<?php

declare(strict_types=1);

namespace App\Procuration\V2;

use App\Entity\Geo\Zone;
use App\Entity\ProcurationV2\Round;
use Doctrine\ORM\EntityManagerInterface;

class ProcurationStatsHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getGlobalStats(Round $round): array
    {
        $sql = <<<'SQL'
            SELECT
            	(
            		SELECT COUNT(DISTINCT(request_slot.id))
            		FROM procuration_v2_request_slot AS request_slot
            		WHERE request_slot.round_id = round.id
            	) AS total_request_slot,
            	(
            		SELECT COUNT(DISTINCT(request_slot.id))
            		FROM procuration_v2_request_slot AS request_slot
            		WHERE request_slot.round_id = round.id
            		AND request_slot.manual = 0
            		AND request_slot.proxy_slot_id IS NULL
            	) AS total_pending_request_slot,
            	(
            		SELECT COUNT(DISTINCT(request_slot.id))
            		FROM procuration_v2_request_slot AS request_slot
            		WHERE request_slot.round_id = round.id
            		AND (
            			request_slot.manual = 1
            			OR request_slot.proxy_slot_id IS NOT NULL
            		)
            	) AS total_completed_request_slot,
            	(
            		SELECT COUNT(DISTINCT(proxy_slot.proxy_id))
            		FROM procuration_v2_proxy_slot AS proxy_slot
            		WHERE proxy_slot.round_id = round.id
            	) AS total_proxy,
            	(
            		SELECT COUNT(DISTINCT(proxy_slot.id))
            		FROM procuration_v2_proxy_slot AS proxy_slot
            		WHERE proxy_slot.round_id = round.id
            	) AS total_proxy_slot
            FROM procuration_v2_rounds AS round
            WHERE round.id = :round_id
            SQL;

        return $this->entityManager
            ->getConnection()
            ->executeQuery($sql, ['round_id' => $round->getId()])
            ->fetchAssociative()
        ;
    }

    public function getZonesStats(Round $round): array
    {
        $sql = <<<'SQL'
            SELECT *
            FROM (
            -- 1. Procurations stats by dpt
            (
            	SELECT
            	    zone.id AS zone_id,
            		zone.type AS zone_type,
            		zone.name AS zone_name,
            		zone.code AS zone_code,
            		IF(LENGTH(zone.code) = 2, 0, 1) AS zone_position,
            		(
            			SELECT
            				COUNT(DISTINCT(request_slot.id))
            			FROM procuration_v2_request_slot AS request_slot
            			INNER JOIN procuration_v2_requests AS request
            				ON request.id = request_slot.request_id
            			INNER JOIN geo_zone AS request_zone
            				ON request_zone.id = request.vote_zone_id
            			INNER JOIN geo_zone_parent AS request_gzp
            				ON request_gzp.child_id = request_zone.id
            			WHERE request_slot.round_id = round.id
            			AND request_zone.type = :zone_type_city
            			AND request_gzp.parent_id = zone.id
            		) AS total_request_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(request_slot.id))
            			FROM procuration_v2_request_slot AS request_slot
            			INNER JOIN procuration_v2_requests AS request
            				ON request.id = request_slot.request_id
            			INNER JOIN geo_zone AS request_zone
            				ON request_zone.id = request.vote_zone_id
            			INNER JOIN geo_zone_parent AS request_gzp
            				ON request_gzp.child_id = request_zone.id
            			WHERE request_slot.round_id = round.id
            			AND request_zone.type = :zone_type_city
            			AND request_gzp.parent_id = zone.id
            			AND (
            				request_slot.manual = 0
            				AND request_slot.proxy_slot_id IS NULL
            			)
            		) AS total_pending_request_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(request_slot.id))
            			FROM procuration_v2_request_slot AS request_slot
            			INNER JOIN procuration_v2_requests AS request
            				ON request.id = request_slot.request_id
            			INNER JOIN geo_zone AS request_zone
            				ON request_zone.id = request.vote_zone_id
            			INNER JOIN geo_zone_parent AS request_gzp
            				ON request_gzp.child_id = request_zone.id
            			WHERE request_slot.round_id = round.id
            			AND request_zone.type = :zone_type_city
            			AND request_gzp.parent_id = zone.id
            			AND (
            				request_slot.manual = 1
            				OR request_slot.proxy_slot_id IS NOT NULL
            			)
            		) AS total_completed_request_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			INNER JOIN geo_zone AS proxy_zone
            				ON proxy_zone.id = proxy.vote_zone_id
            			INNER JOIN geo_zone_parent AS proxy_gzp
            				ON proxy_gzp.child_id = proxy_zone.id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy_zone.type = :zone_type_city
            			AND proxy_gzp.parent_id = zone.id
            		) AS total_proxy,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy_slot.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			INNER JOIN geo_zone AS proxy_zone
            				ON proxy_zone.id = proxy.vote_zone_id
            			INNER JOIN geo_zone_parent AS proxy_gzp
            				ON proxy_gzp.child_id = proxy_zone.id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy_zone.type = :zone_type_city
            			AND proxy_gzp.parent_id = zone.id
            		) AS total_proxy_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy_slot.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			INNER JOIN geo_zone AS proxy_zone
            				ON proxy_zone.id = proxy.vote_zone_id
            			INNER JOIN geo_zone_parent AS proxy_gzp
            				ON proxy_gzp.child_id = proxy_zone.id
            			LEFT JOIN procuration_v2_request_slot AS matched_request_slot
            				ON matched_request_slot.proxy_slot_id = proxy_slot.id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy_zone.type = :zone_type_city
            			AND proxy_gzp.parent_id = zone.id
            			AND (
            				proxy_slot.manual = 0
            				AND matched_request_slot.id IS NULL
            			)
            		) AS total_pending_proxy_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy_slot.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			INNER JOIN geo_zone AS proxy_zone
            				ON proxy_zone.id = proxy.vote_zone_id
            			INNER JOIN geo_zone_parent AS proxy_gzp
            				ON proxy_gzp.child_id = proxy_zone.id
            			LEFT JOIN procuration_v2_request_slot AS matched_request_slot
            				ON matched_request_slot.proxy_slot_id = proxy_slot.id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy_zone.type = :zone_type_city
            			AND proxy_gzp.parent_id = zone.id
            			AND (
            				proxy_slot.manual = 1
            				OR matched_request_slot.id IS NOT NULL
            			)
            		) AS total_completed_proxy_slot
            	FROM geo_zone AS zone
            	INNER JOIN procuration_v2_rounds AS round
            		ON round.id = :round_id
            	WHERE zone.type = :zone_type_department
            	AND zone.code NOT IN (
            		'75', -- Paris will be handled by borough
            		'69M',
            		'69D',
            		'2A',
            		'2B',
            		'64B',
            		'64PB'
            	)
            )
            UNION ALL
            -- 2. Procuration stats by country & borough
            (
            	SELECT
            	    zone.id AS zone_id,
            		zone.type AS zone_type,
            		zone.name AS zone_name,
            		zone.code AS zone_code,
            		IF (zone.type = :zone_type_borough, 2, 3) AS zone_position,
            		(
            			SELECT
            				COUNT(DISTINCT(request_slot.id))
            			FROM procuration_v2_request_slot AS request_slot
            			INNER JOIN procuration_v2_requests AS request
            				ON request.id = request_slot.request_id
            			WHERE request_slot.round_id = round.id
            			AND request.vote_zone_id = zone.id
            		) AS total_request_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(request_slot.id))
            			FROM procuration_v2_request_slot AS request_slot
            			INNER JOIN procuration_v2_requests AS request
            				ON request.id = request_slot.request_id
            			WHERE request_slot.round_id = round.id
            			AND request.vote_zone_id = zone.id
            			AND (
            				request_slot.manual = 0
            				AND request_slot.proxy_slot_id IS NULL
            			)
            		) AS total_pending_request_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(request_slot.id))
            			FROM procuration_v2_request_slot AS request_slot
            			INNER JOIN procuration_v2_requests AS request
            				ON request.id = request_slot.request_id
            			WHERE request_slot.round_id = round.id
            			AND request.vote_zone_id = zone.id
            			AND (
            				request_slot.manual = 1
            				OR request_slot.proxy_slot_id IS NOT NULL
            			)
            		) AS total_completed_request_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy.vote_zone_id = zone.id
            		) AS total_proxy,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy_slot.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy.vote_zone_id = zone.id
            		) AS total_proxy_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy_slot.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			LEFT JOIN procuration_v2_request_slot AS matched_request_slot
            				ON matched_request_slot.proxy_slot_id = proxy_slot.id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy.vote_zone_id = zone.id
            			AND (
            				proxy_slot.manual = 0
            				AND matched_request_slot.id IS NULL
            			)
            		) AS total_pending_proxy_slot,
            		(
            			SELECT
            				COUNT(DISTINCT(proxy_slot.id))
            			FROM procuration_v2_proxy_slot AS proxy_slot
            			INNER JOIN procuration_v2_proxies AS proxy
            				ON proxy.id = proxy_slot.proxy_id
            			LEFT JOIN procuration_v2_request_slot AS matched_request_slot
            				ON matched_request_slot.proxy_slot_id = proxy_slot.id
            			WHERE proxy_slot.round_id = round.id
            			AND proxy.vote_zone_id = zone.id
            			AND (
            				proxy_slot.manual = 1
            				OR matched_request_slot.id IS NOT NULL
            			)
            		) AS total_completed_proxy_slot
            	FROM geo_zone AS zone
            	INNER JOIN procuration_v2_rounds AS round
            		ON round.id = :round_id
            	WHERE zone.type IN (:zone_type_country, :zone_type_borough)
            	AND zone.code != 'FR'
            )
            ) AS procuration_stats
            ORDER BY zone_position, zone_code
            SQL;

        return $this->entityManager
            ->getConnection()
            ->executeQuery($sql, [
                'round_id' => $round->getId(),
                'zone_type_borough' => Zone::BOROUGH,
                'zone_type_city' => Zone::CITY,
                'zone_type_department' => Zone::DEPARTMENT,
                'zone_type_country' => Zone::COUNTRY,
            ])
            ->fetchAllAssociative()
        ;
    }
}
