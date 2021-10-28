<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210303175731 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cause_follower (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          cause_id INT UNSIGNED NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_6F9A854466E2221E (cause_id), 
          INDEX IDX_6F9A854425F06C53 (adherent_id), 
          UNIQUE INDEX cause_follower_unique (cause_id, adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          cause_follower 
        ADD 
          CONSTRAINT FK_6F9A854466E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          cause_follower 
        ADD 
          CONSTRAINT FK_6F9A854425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('DROP TABLE coalition_follower');
        $this->addSql('CREATE TABLE coalition_follower (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          coalition_id INT UNSIGNED NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_DFF370E2C2A46A23 (coalition_id), 
          INDEX IDX_DFF370E225F06C53 (adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          coalition_follower 
        ADD 
          CONSTRAINT FK_DFF370E2C2A46A23 FOREIGN KEY (coalition_id) REFERENCES coalition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          coalition_follower 
        ADD 
          CONSTRAINT FK_DFF370E225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE cause_follower');
        $this->addSql('DROP TABLE coalition_follower');
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
    }
}
