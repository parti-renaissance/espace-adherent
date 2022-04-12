<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220412120724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE legislative_newsletter_subscription (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          from_zone_id INT UNSIGNED DEFAULT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          email_address VARCHAR(255) NOT NULL,
          postal_code VARCHAR(11) NOT NULL,
          country VARCHAR(2) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_2672FB76D17F50A6 (uuid),
          INDEX IDX_2672FB761972DC04 (from_zone_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          legislative_newsletter_subscription
        ADD
          CONSTRAINT FK_2672FB761972DC04 FOREIGN KEY (from_zone_id) REFERENCES geo_zone (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE legislative_newsletter_subscription');
    }
}
