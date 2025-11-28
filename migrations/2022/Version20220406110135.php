<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220406110135 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE invalid_email_address (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          email_hash VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_4792EA85D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE procuration_proxies ADD disabled_reason VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_requests ADD disabled_reason VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE invalid_email_address');
        $this->addSql('ALTER TABLE procuration_proxies DROP disabled_reason');
        $this->addSql('ALTER TABLE procuration_requests DROP disabled_reason');
    }
}
