<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180906231458 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents CHANGE interests interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');

        $this->addSql(<<<'SQL'
            UPDATE adherents
            SET interests =
                CASE
                    WHEN interests LIKE '{%' THEN REPLACE(
                        SUBSTR(JSON_EXTRACT(interests, '$.*') FROM 3 FOR CHAR_LENGTH(JSON_EXTRACT(interests, '$.*')) - 4),
                        '", "',
                        ','
                    )
                    WHEN interests LIKE '[%' THEN REPLACE(
                        SUBSTR(interests FROM 3 FOR CHAR_LENGTH(interests) - 4),
                        '", "',
                        ','
                    )
                    ELSE null
                END
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents CHANGE interests interests JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
    }
}
