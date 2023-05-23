<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230523023528 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_3860914657B96A2D');
        $this->addSql('DROP INDEX IDX_3860914657B96A2D ON elected_representative_mandate');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        CHANGE
          attachment_zone_id attached_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_38609146DAC800AF FOREIGN KEY (attached_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_38609146DAC800AF ON elected_representative_mandate (attached_zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_38609146DAC800AF');
        $this->addSql('DROP INDEX IDX_38609146DAC800AF ON elected_representative_mandate');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        CHANGE
          attached_zone_id attachment_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_3860914657B96A2D FOREIGN KEY (attachment_zone_id) REFERENCES geo_zone (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_3860914657B96A2D ON elected_representative_mandate (attachment_zone_id)');
    }
}
