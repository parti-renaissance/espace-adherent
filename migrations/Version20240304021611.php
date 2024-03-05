<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240304021611 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_v2_elections (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          slug VARCHAR(100) NOT NULL,
          request_title LONGTEXT NOT NULL,
          request_description LONGTEXT NOT NULL,
          request_confirmation LONGTEXT NOT NULL,
          request_legal LONGTEXT NOT NULL,
          proxy_title LONGTEXT NOT NULL,
          proxy_description LONGTEXT NOT NULL,
          proxy_confirmation LONGTEXT NOT NULL,
          proxy_legal LONGTEXT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_B8544E75E237E06 (name),
          UNIQUE INDEX UNIQ_B8544E7989D9B62 (slug),
          UNIQUE INDEX UNIQ_B8544E7D17F50A6 (uuid),
          INDEX IDX_B8544E79DF5350C (created_by_administrator_id),
          INDEX IDX_B8544E7CF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_v2_rounds (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          description LONGTEXT NOT NULL,
          date DATE NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_A2DDD28D17F50A6 (uuid),
          INDEX IDX_A2DDD28A708DAFF (election_id),
          INDEX IDX_A2DDD289DF5350C (created_by_administrator_id),
          INDEX IDX_A2DDD28CF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_v2_elections
        ADD
          CONSTRAINT FK_B8544E79DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_elections
        ADD
          CONSTRAINT FK_B8544E7CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_rounds
        ADD
          CONSTRAINT FK_A2DDD28A708DAFF FOREIGN KEY (election_id) REFERENCES procuration_v2_elections (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_rounds
        ADD
          CONSTRAINT FK_A2DDD289DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_rounds
        ADD
          CONSTRAINT FK_A2DDD28CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_elections DROP FOREIGN KEY FK_B8544E79DF5350C');
        $this->addSql('ALTER TABLE procuration_v2_elections DROP FOREIGN KEY FK_B8544E7CF1918FF');
        $this->addSql('ALTER TABLE procuration_v2_rounds DROP FOREIGN KEY FK_A2DDD28A708DAFF');
        $this->addSql('ALTER TABLE procuration_v2_rounds DROP FOREIGN KEY FK_A2DDD289DF5350C');
        $this->addSql('ALTER TABLE procuration_v2_rounds DROP FOREIGN KEY FK_A2DDD28CF1918FF');
        $this->addSql('DROP TABLE procuration_v2_elections');
        $this->addSql('DROP TABLE procuration_v2_rounds');
    }
}
