<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250506132144 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE agora_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          agora_id INT UNSIGNED NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_9F885CDCD17F50A6 (uuid),
          INDEX IDX_9F885CDC57588F43 (agora_id),
          INDEX IDX_9F885CDC25F06C53 (adherent_id),
          INDEX IDX_9F885CDC9DF5350C (created_by_administrator_id),
          INDEX IDX_9F885CDCCF1918FF (updated_by_administrator_id),
          UNIQUE INDEX UNIQ_9F885CDC57588F4325F06C53 (agora_id, adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          agora_membership
        ADD
          CONSTRAINT FK_9F885CDC57588F43 FOREIGN KEY (agora_id) REFERENCES agora (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          agora_membership
        ADD
          CONSTRAINT FK_9F885CDC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          agora_membership
        ADD
          CONSTRAINT FK_9F885CDC9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          agora_membership
        ADD
          CONSTRAINT FK_9F885CDCCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE agora_membership DROP FOREIGN KEY FK_9F885CDC57588F43');
        $this->addSql('ALTER TABLE agora_membership DROP FOREIGN KEY FK_9F885CDC25F06C53');
        $this->addSql('ALTER TABLE agora_membership DROP FOREIGN KEY FK_9F885CDC9DF5350C');
        $this->addSql('ALTER TABLE agora_membership DROP FOREIGN KEY FK_9F885CDCCF1918FF');
        $this->addSql('DROP TABLE agora_membership');
    }
}
