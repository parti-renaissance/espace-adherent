<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220411164143 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE legislative_newsletter_subscriptions (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          from_zone_id INT UNSIGNED DEFAULT NULL,
          email_address VARCHAR(100) NOT NULL,
          postal_code VARCHAR(11) DEFAULT NULL,
          country VARCHAR(2) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          deleted_at DATETIME DEFAULT NULL,
          first_name VARCHAR(50) NOT NULL,
          last_name VARCHAR(50) NOT NULL,
          UNIQUE INDEX UNIQ_A24E69F9D17F50A6 (uuid),
          INDEX IDX_A24E69F91972DC04 (from_zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          legislative_newsletter_subscriptions
        ADD
          CONSTRAINT FK_A24E69F91972DC04 FOREIGN KEY (from_zone_id) REFERENCES geo_zone (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE legislative_newsletter_subscriptions');
    }
}
