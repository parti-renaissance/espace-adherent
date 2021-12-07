<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211206110133 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_floor_statistics
        ADD
          visited_doors LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
        ADD
          nb_surveys SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX building_block_unique ON pap_building_block (name, building_id)');
        $this->addSql('CREATE UNIQUE INDEX floor_unique ON pap_floor (number, building_block_id)');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        CHANGE
          nb_doors nb_visited_doors SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_floor_statistics DROP visited_doors, DROP nb_surveys');
        $this->addSql('DROP INDEX building_block_unique ON pap_building_block');
        $this->addSql('DROP INDEX floor_unique ON pap_floor');
        $this->addSql('ALTER TABLE
          pap_building_statistics
        CHANGE
          nb_visited_doors nb_doors SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
    }
}
