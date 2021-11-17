<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211117174035 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          building_id INT UNSIGNED NOT NULL,
        CHANGE
          building building_block VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        ADD
          CONSTRAINT FK_5A3F26F74D2A7E12 FOREIGN KEY (building_id) REFERENCES pap_building (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5A3F26F74D2A7E12 ON pap_campaign_history (building_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_campaign_history DROP FOREIGN KEY FK_5A3F26F74D2A7E12');
        $this->addSql('DROP INDEX IDX_5A3F26F74D2A7E12 ON pap_campaign_history');
        $this->addSql('ALTER TABLE
          pap_campaign_history
        DROP
          building_id,
        CHANGE
          building_block building VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
