<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210318134314 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE poll_choice (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          poll_id INT UNSIGNED NOT NULL, 
          value VARCHAR(255) NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_2DAE19C93C947C0F (poll_id), 
          UNIQUE INDEX poll_choice_uuid_unique (uuid), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poll_vote (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          choice_id INT UNSIGNED NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          device_id INT UNSIGNED DEFAULT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_ED568EBE998666D1 (choice_id), 
          INDEX IDX_ED568EBE25F06C53 (adherent_id), 
          INDEX IDX_ED568EBE94A4C7D4 (device_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE poll (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          created_by_id INT DEFAULT NULL, 
          question VARCHAR(255) NOT NULL, 
          finish_at DATETIME NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_84BCFA45B03A8386 (created_by_id), 
          UNIQUE INDEX poll_uuid_unique (uuid), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          poll_choice 
        ADD 
          CONSTRAINT FK_2DAE19C93C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          poll_vote 
        ADD 
          CONSTRAINT FK_ED568EBE998666D1 FOREIGN KEY (choice_id) REFERENCES poll_choice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          poll_vote 
        ADD 
          CONSTRAINT FK_ED568EBE25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          poll_vote 
        ADD 
          CONSTRAINT FK_ED568EBE94A4C7D4 FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          poll 
        ADD 
          CONSTRAINT FK_84BCFA45B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE998666D1');
        $this->addSql('ALTER TABLE poll_choice DROP FOREIGN KEY FK_2DAE19C93C947C0F');
        $this->addSql('DROP TABLE poll_choice');
        $this->addSql('DROP TABLE poll_vote');
        $this->addSql('DROP TABLE poll');
    }
}
