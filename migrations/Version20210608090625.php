<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210608090625 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE device_zone (
          device_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_29D2153D94A4C7D4 (device_id),
          INDEX IDX_29D2153D9F2C3FAB (zone_id),
          PRIMARY KEY(device_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE push_token (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          device_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          identifier VARCHAR(255) NOT NULL,
          source VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_51BC1381772E836A (identifier),
          INDEX IDX_51BC138125F06C53 (adherent_id),
          INDEX IDX_51BC138194A4C7D4 (device_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          device_zone
        ADD
          CONSTRAINT FK_29D2153D94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          device_zone
        ADD
          CONSTRAINT FK_29D2153D9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          CONSTRAINT FK_51BC138125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          CONSTRAINT FK_51BC138194A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE devices ADD postal_code VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE device_zone');
        $this->addSql('DROP TABLE push_token');
        $this->addSql('ALTER TABLE devices DROP postal_code');
    }
}
