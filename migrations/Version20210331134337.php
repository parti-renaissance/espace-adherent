<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210331134337 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cause ADD canonical_name VARCHAR(255) DEFAULT NULL, ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql(<<<SQL
UPDATE cause SET canonical_name = LOWER(name), slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(name), ':', ''), ')', ''), '(', ''), ',', ''), '\\\', ''), '/', ''), '"', ''), '?', ''), '\'', ''), '&', ''), '!', ''), '.', ''), ' ', '-'), '--', '-'), '--', '-'))
SQL
        );

        $this->addSql('ALTER TABLE
          cause
        CHANGE
          canonical_name canonical_name VARCHAR(255) NOT NULL,
        CHANGE
          slug slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cause DROP canonical_name, DROP slug');
    }
}
