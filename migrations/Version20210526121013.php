<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210526121013 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226F39192B5C');
        $this->addSql('DROP INDEX UNIQ_4E74226F39192B5C ON jecoute_region');
        $this->addSql('ALTER TABLE jecoute_region CHANGE geo_region_id zone_id INT UNSIGNED NOT NULL');
        $this->addSql('UPDATE jecoute_region as r
    INNER JOIN geo_region gr on r.zone_id = gr.id
    INNER JOIN geo_zone gz on gr.code = gz.code
SET r.zone_id = gz.id');
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          CONSTRAINT FK_4E74226F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E74226F9F2C3FAB ON jecoute_region (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_region DROP FOREIGN KEY FK_4E74226F9F2C3FAB');
        $this->addSql('DROP INDEX UNIQ_4E74226F9F2C3FAB ON jecoute_region');
        $this->addSql('ALTER TABLE jecoute_region CHANGE zone_id geo_region_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          jecoute_region
        ADD
          CONSTRAINT FK_4E74226F39192B5C FOREIGN KEY (geo_region_id) REFERENCES geo_region (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4E74226F39192B5C ON jecoute_region (geo_region_id)');
    }
}
