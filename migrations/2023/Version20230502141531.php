<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230502141531 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_mandate ADD attachment_zone_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          elected_representative_mandate
        ADD
          CONSTRAINT FK_3860914657B96A2D FOREIGN KEY (attachment_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('CREATE INDEX IDX_3860914657B96A2D ON elected_representative_mandate (attachment_zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_mandate DROP FOREIGN KEY FK_3860914657B96A2D');
        $this->addSql('DROP INDEX IDX_3860914657B96A2D ON elected_representative_mandate');
        $this->addSql('ALTER TABLE elected_representative_mandate DROP attachment_zone_id');
    }
}
