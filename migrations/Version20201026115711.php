<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201026115711 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_tags ADD zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          referent_tags
        ADD
          CONSTRAINT FK_135D29D99F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_135D29D99F2C3FAB ON referent_tags (zone_id)');
        $this->addSql('ALTER TABLE geo_zone DROP FOREIGN KEY FK_A4CCEF079C262DB3');
        $this->addSql('DROP INDEX IDX_A4CCEF079C262DB3 ON geo_zone');
        $this->addSql('ALTER TABLE geo_zone DROP referent_tag_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE geo_zone ADD referent_tag_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          geo_zone
        ADD
          CONSTRAINT FK_A4CCEF079C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_A4CCEF079C262DB3 ON geo_zone (referent_tag_id)');
        $this->addSql('ALTER TABLE referent_tags DROP FOREIGN KEY FK_135D29D99F2C3FAB');
        $this->addSql('DROP INDEX IDX_135D29D99F2C3FAB ON referent_tags');
        $this->addSql('ALTER TABLE referent_tags DROP zone_id');
    }
}
