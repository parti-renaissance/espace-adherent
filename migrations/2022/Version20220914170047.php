<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220914170047 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE renaissance_newsletter_subscription (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          first_name VARCHAR(255) NOT NULL,
          zip_code VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          confirmed_at DATETIME DEFAULT NULL,
          token CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_46DB1A77E7927C74 (email),
          UNIQUE INDEX UNIQ_46DB1A77D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE renaissance_newsletter_subscription');
    }
}
