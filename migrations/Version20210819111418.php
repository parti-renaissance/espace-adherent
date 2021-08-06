<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210819111418 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE member (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          team_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_70E4FA78296CD8AE (team_id),
          INDEX IDX_70E4FA7825F06C53 (adherent_id),
          UNIQUE INDEX team_member_unique (team_id, adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          administrator_id INT DEFAULT NULL,
          type VARCHAR(10) NOT NULL,
          name VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_C4E0A61F4B09E92C (administrator_id),
          UNIQUE INDEX team_type_name_unique (type, name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team_member_histories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          team_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          administrator_id INT DEFAULT NULL,
          action VARCHAR(20) NOT NULL,
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          INDEX IDX_38471A47296CD8AE (team_id),
          INDEX team_member_histories_adherent_id_idx (adherent_id),
          INDEX team_member_histories_administrator_id_idx (administrator_id),
          INDEX team_member_histories_date_idx (date),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          member
        ADD
          CONSTRAINT FK_70E4FA78296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          member
        ADD
          CONSTRAINT FK_70E4FA7825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          team
        ADD
          CONSTRAINT FK_C4E0A61F4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          team_member_histories
        ADD
          CONSTRAINT FK_38471A47296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          team_member_histories
        ADD
          CONSTRAINT FK_38471A4725F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          team_member_histories
        ADD
          CONSTRAINT FK_38471A474B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE member DROP FOREIGN KEY FK_70E4FA78296CD8AE');
        $this->addSql('ALTER TABLE team_member_histories DROP FOREIGN KEY FK_38471A47296CD8AE');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE team_member_histories');
    }
}
