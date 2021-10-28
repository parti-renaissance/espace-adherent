<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210211183646 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE coalition (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          description LONGTEXT NOT NULL, 
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          UNIQUE INDEX coalition_uuid_unique (uuid), 
          UNIQUE INDEX coalition_name_unique (name), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coalition_follower (
          coalition_id INT UNSIGNED NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          INDEX IDX_DFF370E2C2A46A23 (coalition_id), 
          INDEX IDX_DFF370E225F06C53 (adherent_id), 
          PRIMARY KEY(coalition_id, adherent_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          coalition_follower 
        ADD 
          CONSTRAINT FK_DFF370E2C2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          coalition_follower 
        ADD 
          CONSTRAINT FK_DFF370E225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events ADD coalition_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          events 
        ADD 
          CONSTRAINT FK_5387574AC2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id)');
        $this->addSql('CREATE INDEX IDX_5387574AC2A46A23 ON events (coalition_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE coalition_follower DROP FOREIGN KEY FK_DFF370E2C2A46A23');
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AC2A46A23');
        $this->addSql('DROP TABLE coalition');
        $this->addSql('DROP TABLE coalition_follower');
        $this->addSql('DROP INDEX IDX_5387574AC2A46A23 ON events');
        $this->addSql('ALTER TABLE events DROP coalition_id');
    }
}
