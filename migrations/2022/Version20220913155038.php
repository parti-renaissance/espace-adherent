<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220913155038 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD activism_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA38C8E414F FOREIGN KEY (activism_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_562C7DA38C8E414F ON adherents (activism_zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA38C8E414F');
        $this->addSql('DROP INDEX IDX_562C7DA38C8E414F ON adherents');
        $this->addSql('ALTER TABLE adherents DROP activism_zone_id');
    }
}
