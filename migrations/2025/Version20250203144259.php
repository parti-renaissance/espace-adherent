<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250203144259 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EF94819E3B');
        $this->addSql('DROP INDEX IDX_F66947EF94819E3B ON general_convention');
        $this->addSql('UPDATE general_convention SET committee_zone_id = NULL');
        $this->addSql('ALTER TABLE general_convention CHANGE committee_zone_id committee_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EFED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_F66947EFED1A100B ON general_convention (committee_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EFED1A100B');
        $this->addSql('DROP INDEX IDX_F66947EFED1A100B ON general_convention');
        $this->addSql('ALTER TABLE general_convention CHANGE committee_id committee_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EF94819E3B FOREIGN KEY (committee_zone_id) REFERENCES geo_zone (id) ON
        UPDATE
          NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F66947EF94819E3B ON general_convention (committee_zone_id)');
    }
}
