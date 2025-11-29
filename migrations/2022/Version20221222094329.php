<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221222094329 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE department_site (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          content LONGTEXT NOT NULL,
          slug VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          json_content LONGTEXT DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_CB596EB1989D9B62 (slug),
          UNIQUE INDEX UNIQ_CB596EB1D17F50A6 (uuid),
          UNIQUE INDEX UNIQ_CB596EB19F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          department_site
        ADD
          CONSTRAINT FK_CB596EB19F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE department_site');
    }
}
