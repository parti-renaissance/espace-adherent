<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240329140615 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_v2_matching_history (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          request_id INT UNSIGNED NOT NULL,
          proxy_id INT UNSIGNED NOT NULL,
          matcher_id INT UNSIGNED DEFAULT NULL,
          admin_matcher_id INT DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          created_at DATETIME NOT NULL,
          INDEX IDX_4B792213427EB8A5 (request_id),
          INDEX IDX_4B792213DB26A4E (proxy_id),
          INDEX IDX_4B792213F38CBA7C (matcher_id),
          INDEX IDX_4B7922133BB21CF9 (admin_matcher_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_v2_matching_history
        ADD
          CONSTRAINT FK_4B792213427EB8A5 FOREIGN KEY (request_id) REFERENCES procuration_v2_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_matching_history
        ADD
          CONSTRAINT FK_4B792213DB26A4E FOREIGN KEY (proxy_id) REFERENCES procuration_v2_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_v2_matching_history
        ADD
          CONSTRAINT FK_4B792213F38CBA7C FOREIGN KEY (matcher_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_matching_history
        ADD
          CONSTRAINT FK_4B7922133BB21CF9 FOREIGN KEY (admin_matcher_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_matching_history DROP FOREIGN KEY FK_4B792213427EB8A5');
        $this->addSql('ALTER TABLE procuration_v2_matching_history DROP FOREIGN KEY FK_4B792213DB26A4E');
        $this->addSql('ALTER TABLE procuration_v2_matching_history DROP FOREIGN KEY FK_4B792213F38CBA7C');
        $this->addSql('ALTER TABLE procuration_v2_matching_history DROP FOREIGN KEY FK_4B7922133BB21CF9');
        $this->addSql('DROP TABLE procuration_v2_matching_history');
    }
}
