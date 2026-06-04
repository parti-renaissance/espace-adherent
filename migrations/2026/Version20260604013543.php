<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260604013543 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE timeline_feed (
                  type VARCHAR(50) NOT NULL,
                  publication_date DATETIME NOT NULL,
                  event_date DATETIME DEFAULT NULL,
                  audience JSON DEFAULT NULL,
                  display JSON NOT NULL,
                  author_importance SMALLINT DEFAULT 1 NOT NULL,
                  updated_at DATETIME NOT NULL,
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  uuid CHAR(36) NOT NULL,
                  UNIQUE INDEX UNIQ_2248B1DED17F50A6 (uuid),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE timeline_feed');
    }
}
