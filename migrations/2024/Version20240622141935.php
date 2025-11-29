<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240622141935 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_v2_proxy_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          proxy_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          author_administrator_id INT DEFAULT NULL,
          context JSON DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL,
          author_scope VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_B35088FD17F50A6 (uuid),
          INDEX IDX_B35088FDB26A4E (proxy_id),
          INDEX IDX_B35088FF675F31B (author_id),
          INDEX IDX_B35088F9301170 (author_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_v2_proxy_slot_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          proxy_slot_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          author_administrator_id INT DEFAULT NULL,
          context JSON DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL,
          author_scope VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_535D3E99D17F50A6 (uuid),
          INDEX IDX_535D3E994FCCD8F9 (proxy_slot_id),
          INDEX IDX_535D3E99F675F31B (author_id),
          INDEX IDX_535D3E999301170 (author_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_v2_request_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          request_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          author_administrator_id INT DEFAULT NULL,
          context JSON DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL,
          author_scope VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_24E294ABD17F50A6 (uuid),
          INDEX IDX_24E294AB427EB8A5 (request_id),
          INDEX IDX_24E294ABF675F31B (author_id),
          INDEX IDX_24E294AB9301170 (author_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE procuration_v2_request_slot_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          request_slot_id INT UNSIGNED NOT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          author_administrator_id INT DEFAULT NULL,
          context JSON DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          date DATETIME NOT NULL,
          author_scope VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_A50F299D17F50A6 (uuid),
          INDEX IDX_A50F29973C163CB (request_slot_id),
          INDEX IDX_A50F299F675F31B (author_id),
          INDEX IDX_A50F2999301170 (author_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_action
        ADD
          CONSTRAINT FK_B35088FDB26A4E FOREIGN KEY (proxy_id) REFERENCES procuration_v2_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_action
        ADD
          CONSTRAINT FK_B35088FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_action
        ADD
          CONSTRAINT FK_B35088F9301170 FOREIGN KEY (author_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot_action
        ADD
          CONSTRAINT FK_535D3E994FCCD8F9 FOREIGN KEY (proxy_slot_id) REFERENCES procuration_v2_proxy_slot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot_action
        ADD
          CONSTRAINT FK_535D3E99F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot_action
        ADD
          CONSTRAINT FK_535D3E999301170 FOREIGN KEY (author_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_action
        ADD
          CONSTRAINT FK_24E294AB427EB8A5 FOREIGN KEY (request_id) REFERENCES procuration_v2_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_request_action
        ADD
          CONSTRAINT FK_24E294ABF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_action
        ADD
          CONSTRAINT FK_24E294AB9301170 FOREIGN KEY (author_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot_action
        ADD
          CONSTRAINT FK_A50F29973C163CB FOREIGN KEY (request_slot_id) REFERENCES procuration_v2_request_slot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot_action
        ADD
          CONSTRAINT FK_A50F299F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot_action
        ADD
          CONSTRAINT FK_A50F2999301170 FOREIGN KEY (author_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP FOREIGN KEY FK_87509068F38CBA7C');
        $this->addSql('DROP INDEX IDX_87509068F38CBA7C ON procuration_v2_proxy_slot');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot DROP matcher_id, DROP matched_at');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP FOREIGN KEY FK_DA56A35FF38CBA7C');
        $this->addSql('DROP INDEX IDX_DA56A35FF38CBA7C ON procuration_v2_request_slot');
        $this->addSql('ALTER TABLE procuration_v2_request_slot DROP matcher_id, DROP matched_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxy_action DROP FOREIGN KEY FK_B35088FDB26A4E');
        $this->addSql('ALTER TABLE procuration_v2_proxy_action DROP FOREIGN KEY FK_B35088FF675F31B');
        $this->addSql('ALTER TABLE procuration_v2_proxy_action DROP FOREIGN KEY FK_B35088F9301170');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot_action DROP FOREIGN KEY FK_535D3E994FCCD8F9');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot_action DROP FOREIGN KEY FK_535D3E99F675F31B');
        $this->addSql('ALTER TABLE procuration_v2_proxy_slot_action DROP FOREIGN KEY FK_535D3E999301170');
        $this->addSql('ALTER TABLE procuration_v2_request_action DROP FOREIGN KEY FK_24E294AB427EB8A5');
        $this->addSql('ALTER TABLE procuration_v2_request_action DROP FOREIGN KEY FK_24E294ABF675F31B');
        $this->addSql('ALTER TABLE procuration_v2_request_action DROP FOREIGN KEY FK_24E294AB9301170');
        $this->addSql('ALTER TABLE procuration_v2_request_slot_action DROP FOREIGN KEY FK_A50F29973C163CB');
        $this->addSql('ALTER TABLE procuration_v2_request_slot_action DROP FOREIGN KEY FK_A50F299F675F31B');
        $this->addSql('ALTER TABLE procuration_v2_request_slot_action DROP FOREIGN KEY FK_A50F2999301170');
        $this->addSql('DROP TABLE procuration_v2_proxy_action');
        $this->addSql('DROP TABLE procuration_v2_proxy_slot_action');
        $this->addSql('DROP TABLE procuration_v2_request_action');
        $this->addSql('DROP TABLE procuration_v2_request_slot_action');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          matcher_id INT UNSIGNED DEFAULT NULL,
        ADD
          matched_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_proxy_slot
        ADD
          CONSTRAINT FK_87509068F38CBA7C FOREIGN KEY (matcher_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_87509068F38CBA7C ON procuration_v2_proxy_slot (matcher_id)');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          matcher_id INT UNSIGNED DEFAULT NULL,
        ADD
          matched_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_request_slot
        ADD
          CONSTRAINT FK_DA56A35FF38CBA7C FOREIGN KEY (matcher_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_DA56A35FF38CBA7C ON procuration_v2_request_slot (matcher_id)');
    }
}
