<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250430141243 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE agora (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          president_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          description LONGTEXT DEFAULT NULL,
          max_members_count INT UNSIGNED DEFAULT 50 NOT NULL,
          published TINYINT(1) DEFAULT 1 NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          name VARCHAR(255) NOT NULL,
          canonical_name VARCHAR(255) NOT NULL,
          slug VARCHAR(255) NOT NULL,
          UNIQUE INDEX UNIQ_A0B6A0FDD17F50A6 (uuid),
          INDEX IDX_A0B6A0FDB40A33C7 (president_id),
          INDEX IDX_A0B6A0FD9DF5350C (created_by_administrator_id),
          INDEX IDX_A0B6A0FDCF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agora_general_secretaries (
          agora_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          INDEX IDX_18E675D157588F43 (agora_id),
          INDEX IDX_18E675D125F06C53 (adherent_id),
          PRIMARY KEY(agora_id, adherent_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          agora
        ADD
          CONSTRAINT FK_A0B6A0FDB40A33C7 FOREIGN KEY (president_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          agora
        ADD
          CONSTRAINT FK_A0B6A0FD9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          agora
        ADD
          CONSTRAINT FK_A0B6A0FDCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          agora_general_secretaries
        ADD
          CONSTRAINT FK_18E675D157588F43 FOREIGN KEY (agora_id) REFERENCES agora (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          agora_general_secretaries
        ADD
          CONSTRAINT FK_18E675D125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE agora DROP FOREIGN KEY FK_A0B6A0FDB40A33C7');
        $this->addSql('ALTER TABLE agora DROP FOREIGN KEY FK_A0B6A0FD9DF5350C');
        $this->addSql('ALTER TABLE agora DROP FOREIGN KEY FK_A0B6A0FDCF1918FF');
        $this->addSql('ALTER TABLE agora_general_secretaries DROP FOREIGN KEY FK_18E675D157588F43');
        $this->addSql('ALTER TABLE agora_general_secretaries DROP FOREIGN KEY FK_18E675D125F06C53');
        $this->addSql('DROP TABLE agora');
        $this->addSql('DROP TABLE agora_general_secretaries');
    }
}
