<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250128150801 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE petition_signature (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          civility VARCHAR(255) NOT NULL,
          first_name VARCHAR(255) NOT NULL,
          last_name VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          postal_code VARCHAR(255) NOT NULL,
          phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\',
          petition_name VARCHAR(255) NOT NULL,
          petition_slug VARCHAR(255) NOT NULL,
          validated_at DATETIME DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          utm_source VARCHAR(255) DEFAULT NULL,
          utm_campaign VARCHAR(255) DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_347C2710D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE petition_signature');
    }
}
