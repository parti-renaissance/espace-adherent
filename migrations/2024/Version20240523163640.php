<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240523163640 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vox_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          `type` VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL,
          description LONGTEXT DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          address_address VARCHAR(150) DEFAULT NULL,
          address_postal_code VARCHAR(15) DEFAULT NULL,
          address_city_insee VARCHAR(15) DEFAULT NULL,
          address_city_name VARCHAR(255) DEFAULT NULL,
          address_country VARCHAR(2) DEFAULT NULL,
          address_additional_address VARCHAR(150) DEFAULT NULL,
          address_region VARCHAR(255) DEFAULT NULL,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_geocodable_hash VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_C721ED96D17F50A6 (uuid),
          INDEX IDX_C721ED96F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vox_action_zone (
          action_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_3AA996179D32F035 (action_id),
          INDEX IDX_3AA996179F2C3FAB (zone_id),
          PRIMARY KEY(action_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vox_action_participant (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          action_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          is_present TINYINT(1) DEFAULT 0 NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_9A5816DCD17F50A6 (uuid),
          INDEX IDX_9A5816DC9D32F035 (action_id),
          INDEX IDX_9A5816DC25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          vox_action
        ADD
          CONSTRAINT FK_C721ED96F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          vox_action_zone
        ADD
          CONSTRAINT FK_3AA996179D32F035 FOREIGN KEY (action_id) REFERENCES vox_action (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vox_action_zone
        ADD
          CONSTRAINT FK_3AA996179F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          vox_action_participant
        ADD
          CONSTRAINT FK_9A5816DC9D32F035 FOREIGN KEY (action_id) REFERENCES vox_action (id)');
        $this->addSql('ALTER TABLE
          vox_action_participant
        ADD
          CONSTRAINT FK_9A5816DC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vox_action DROP FOREIGN KEY FK_C721ED96F675F31B');
        $this->addSql('ALTER TABLE vox_action_zone DROP FOREIGN KEY FK_3AA996179D32F035');
        $this->addSql('ALTER TABLE vox_action_zone DROP FOREIGN KEY FK_3AA996179F2C3FAB');
        $this->addSql('ALTER TABLE vox_action_participant DROP FOREIGN KEY FK_9A5816DC9D32F035');
        $this->addSql('ALTER TABLE vox_action_participant DROP FOREIGN KEY FK_9A5816DC25F06C53');
        $this->addSql('DROP TABLE vox_action');
        $this->addSql('DROP TABLE vox_action_zone');
        $this->addSql('DROP TABLE vox_action_participant');
    }
}
