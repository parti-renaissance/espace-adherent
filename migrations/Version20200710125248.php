<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200710125248 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representative_zone_parent (
          child_id INT NOT NULL,
          parent_id INT NOT NULL,
          INDEX IDX_CECA906FDD62C21B (child_id),
          INDEX IDX_CECA906F727ACA70 (parent_id),
          PRIMARY KEY(child_id, parent_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          elected_representative_zone_parent
        ADD
          CONSTRAINT FK_CECA906FDD62C21B FOREIGN KEY (child_id) REFERENCES elected_representative_zone (id)');
        $this->addSql('ALTER TABLE
          elected_representative_zone_parent
        ADD
          CONSTRAINT FK_CECA906F727ACA70 FOREIGN KEY (parent_id) REFERENCES elected_representative_zone (id)');
        $this->addSql('CREATE UNIQUE INDEX elected_representative_zone_code_category_unique ON elected_representative_zone (code, category_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representative_zone_parent');
        $this->addSql('DROP INDEX elected_representative_zone_code_category_unique ON elected_representative_zone');
    }
}
