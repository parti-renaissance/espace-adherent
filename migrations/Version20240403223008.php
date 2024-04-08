<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240403223008 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE besoindeurope_inscription_requests (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          email VARCHAR(255) NOT NULL,
          client_ip VARCHAR(50) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          utm_source VARCHAR(255) DEFAULT NULL,
          utm_campaign VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_96473AF5D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE besoindeurope_inscription_requests');
    }
}
