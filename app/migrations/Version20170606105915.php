<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170606105915 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql(<<<EOSQL
CREATE VIEW unprocessed_proxy_requests_list
AS SELECT pr.id AS id,
    pr.vote_postal_code AS postal_code,
    pr.vote_city_name AS city,
    pr.vote_country AS country,
	CONCAT_WS(" ", pr.last_name, pr.first_names) AS full_name,
	CONCAT_WS(" ", pr.vote_postal_code, pr.vote_city_name, pr.vote_country) AS place,
	COUNT(pp.id) AS matches,
	pr.created_at AS created_at
FROM procuration_requests pr
LEFT JOIN procuration_proxies AS pp ON pr.vote_country = pp.vote_country AND SUBSTRING(pr.vote_postal_code, 0, 2) = SUBSTRING(pp.vote_postal_code, 0, 2)
WHERE pr.processed = 0 AND pr.processed_at IS NULL
GROUP BY pr.id
EOSQL
        );
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP VIEW unprocessed_proxy_requests_list');
    }
}
