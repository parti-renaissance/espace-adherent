<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240614123828 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_v2_proxy_slot (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          proxy_id INT UNSIGNED NOT NULL,
          round_id INT UNSIGNED NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_87509068D17F50A6 (uuid),
          INDEX IDX_875090689DF5350C (created_by_administrator_id),
          INDEX IDX_87509068CF1918FF (updated_by_administrator_id),
          INDEX IDX_87509068DB26A4E (proxy_id),
          INDEX IDX_87509068A6005CA0 (round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_v2_request_slot (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          request_id INT UNSIGNED NOT NULL,
          proxy_slot_id INT UNSIGNED DEFAULT NULL,
          round_id INT UNSIGNED NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_DA56A35FD17F50A6 (uuid),
          INDEX IDX_DA56A35F9DF5350C (created_by_administrator_id),
          INDEX IDX_DA56A35FCF1918FF (updated_by_administrator_id),
          INDEX IDX_DA56A35F427EB8A5 (request_id),
          UNIQUE INDEX UNIQ_DA56A35F4FCCD8F9 (proxy_slot_id),
          INDEX IDX_DA56A35FA6005CA0 (round_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          CONSTRAINT FK_875090689DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          CONSTRAINT FK_87509068CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          CONSTRAINT FK_87509068DB26A4E FOREIGN KEY (proxy_id) REFERENCES procuration_v2_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          CONSTRAINT FK_87509068A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id)');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          CONSTRAINT FK_DA56A35F9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          CONSTRAINT FK_DA56A35FCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          CONSTRAINT FK_DA56A35F427EB8A5 FOREIGN KEY (request_id) REFERENCES procuration_v2_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          CONSTRAINT FK_DA56A35F4FCCD8F9 FOREIGN KEY (proxy_slot_id) REFERENCES procuration_v2_proxy_slot (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          CONSTRAINT FK_DA56A35FA6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP FOREIGN KEY FK_875090689DF5350C');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP FOREIGN KEY FK_87509068CF1918FF');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP FOREIGN KEY FK_87509068DB26A4E');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP FOREIGN KEY FK_87509068A6005CA0');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP FOREIGN KEY FK_DA56A35F9DF5350C');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP FOREIGN KEY FK_DA56A35FCF1918FF');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP FOREIGN KEY FK_DA56A35F427EB8A5');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP FOREIGN KEY FK_DA56A35F4FCCD8F9');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP FOREIGN KEY FK_DA56A35FA6005CA0');
        $this->addSql('DROP TABLE procuration_v2_proxy_slot');
        $this->addSql('DROP TABLE procuration_v2_request_slot');
    }
}
