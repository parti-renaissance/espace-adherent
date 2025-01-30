<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220407142612 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_vote_place ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          CONSTRAINT FK_E143383F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_E143383F9F2C3FAB ON pap_vote_place (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_vote_place DROP FOREIGN KEY FK_E143383F9F2C3FAB');
        $this->addSql('DROP INDEX IDX_E143383F9F2C3FAB ON pap_vote_place');
        $this->addSql('ALTER TABLE pap_vote_place DROP zone_id');
    }
}
