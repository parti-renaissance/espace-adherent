<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201028145944 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_zone ADD geo_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_zone
        ADD
          CONSTRAINT FK_C52FC4A7283AB2A9 FOREIGN KEY (geo_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_C52FC4A7283AB2A9 ON elected_representative_zone (geo_zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_zone DROP FOREIGN KEY FK_C52FC4A7283AB2A9');
        $this->addSql('DROP INDEX IDX_C52FC4A7283AB2A9 ON elected_representative_zone');
        $this->addSql('ALTER TABLE elected_representative_zone DROP geo_zone_id');
    }
}
