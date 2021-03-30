<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201014175014 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_mandate ADD geo_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_38609146283AB2A9 FOREIGN KEY (geo_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_38609146283AB2A9 ON elected_representative_mandate (geo_zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_38609146283AB2A9');
        $this->addSql('DROP INDEX IDX_38609146283AB2A9 ON elected_representative_mandate');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP geo_zone_id');
    }
}
