<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221219134642 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE local_site (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          zone_id INT UNSIGNED DEFAULT NULL,
          content LONGTEXT NOT NULL,
          json_content LONGTEXT DEFAULT NULL,
          slug VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_555C6337989D9B62 (slug),
          UNIQUE INDEX UNIQ_555C6337D17F50A6 (uuid),
          UNIQUE INDEX UNIQ_555C63379F2C3FAB (zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          local_site
        ADD
          CONSTRAINT FK_555C63379F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE local_site');
    }
}
