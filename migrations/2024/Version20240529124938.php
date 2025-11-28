<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240529124938 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA09DF5350C');
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA0CF1918FF');
        $this->addSql('ALTER TABLE adherent_email_subscribe_token DROP FOREIGN KEY FK_376DBA0F675F31B');
        $this->addSql('DROP TABLE adherent_email_subscribe_token');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_email_subscribe_token (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          adherent_uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          value VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          created_at DATETIME NOT NULL,
          expired_at DATETIME NOT NULL,
          used_at DATETIME DEFAULT NULL,
          trigger_source VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_376DBA01D775834 (value),
          UNIQUE INDEX UNIQ_376DBA01D7758346D804024 (value, adherent_uuid),
          UNIQUE INDEX UNIQ_376DBA0D17F50A6 (uuid),
          INDEX IDX_376DBA09DF5350C (created_by_administrator_id),
          INDEX IDX_376DBA0CF1918FF (updated_by_administrator_id),
          INDEX IDX_376DBA0F675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA09DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
    }
}
