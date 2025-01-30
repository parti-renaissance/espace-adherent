<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240514084526 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_building_block_statistics ADD status_detail VARCHAR(25) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_building_event
        ADD
          close_type VARCHAR(255) DEFAULT NULL,
        ADD
          programs SMALLINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        ADD
          nb_distributed_programs SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
        ADD
          status_detail VARCHAR(25) DEFAULT NULL');
        $this->addSql('ALTER TABLE pap_floor_statistics ADD status_detail VARCHAR(25) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_building_block_statistics DROP status_detail');
        $this->addSql('ALTER TABLE pap_building_event DROP close_type, DROP programs');
        $this->addSql('ALTER TABLE pap_building_statistics DROP nb_distributed_programs, DROP status_detail');
        $this->addSql('ALTER TABLE pap_floor_statistics DROP status_detail');
    }
}
