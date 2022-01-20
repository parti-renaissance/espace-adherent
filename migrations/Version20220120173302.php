<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220120173302 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE my_team (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          owner_id INT UNSIGNED NOT NULL,
          scope VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_4C78F4BD17F50A6 (uuid),
          INDEX IDX_4C78F4B7E3C61F9 (owner_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE my_team_member (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          team_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          role VARCHAR(255) NOT NULL,
          scope_features LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_F46A39E9D17F50A6 (uuid),
          INDEX IDX_F46A39E9296CD8AE (team_id),
          INDEX IDX_F46A39E925F06C53 (adherent_id),
          UNIQUE INDEX team_member_unique (team_id, adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          my_team
        ADD
          CONSTRAINT FK_4C78F4B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          my_team_member
        ADD
          CONSTRAINT FK_F46A39E9296CD8AE FOREIGN KEY (team_id) REFERENCES my_team (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          my_team_member
        ADD
          CONSTRAINT FK_F46A39E925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE my_team_member DROP FOREIGN KEY FK_F46A39E9296CD8AE');
        $this->addSql('DROP TABLE my_team');
        $this->addSql('DROP TABLE my_team_member');
    }
}
