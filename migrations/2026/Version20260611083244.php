<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611083244 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE timeline_hidden_feed (
                  hidden_at DATETIME NOT NULL,
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  uuid CHAR(36) NOT NULL,
                  UNIQUE INDEX UNIQ_D9B80285D17F50A6 (uuid),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE timeline_hidden_feed');
    }
}
