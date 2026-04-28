<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260428110842 extends AbstractMigration
{
    private const NATIONAL_SCOPE_CODES = [
        'national',
        'national_communication',
        'pap_national_manager',
        'phoning_national_manager',
        'national_territories_division',
        'national_elected_representatives_division',
        'national_formation_division',
        'national_ideas_division',
        'national_tech_division',
    ];

    public function up(Schema $schema): void
    {
        $codes = "'".implode("', '", self::NATIONAL_SCOPE_CODES)."'";

        $this->addSql("
            UPDATE scope
            SET features = CASE
                WHEN features IS NULL OR features = '' THEN 'publications_cadres'
                WHEN FIND_IN_SET('publications_cadres', features) > 0 THEN features
                ELSE CONCAT(features, ',publications_cadres')
            END
            WHERE code IN ($codes)
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            UPDATE scope
            SET features = NULLIF(TRIM(BOTH ',' FROM REPLACE(CONCAT(',', features, ','), ',publications_cadres,', ',')), '')
            WHERE FIND_IN_SET('publications_cadres', features) > 0
        ");
    }
}
