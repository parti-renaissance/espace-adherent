<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211005182707 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_email_subscribe_token (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          adherent_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          value VARCHAR(40) NOT NULL,
          created_at DATETIME NOT NULL,
          expired_at DATETIME NOT NULL,
          used_at DATETIME DEFAULT NULL,
          trigger_source VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_376DBA0F675F31B (author_id),
          INDEX IDX_376DBA09DF5350C (created_by_administrator_id),
          INDEX IDX_376DBA0CF1918FF (updated_by_administrator_id),
          UNIQUE INDEX UNIQ_376DBA01D775834 (value),
          UNIQUE INDEX UNIQ_376DBA01D7758346D804024 (value, adherent_uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA09DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          adherent_email_subscribe_token
        ADD
          CONSTRAINT FK_376DBA0CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_email_subscribe_token');
    }
}
