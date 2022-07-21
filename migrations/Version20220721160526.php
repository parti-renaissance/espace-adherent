<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220721160526 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE commitment (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          title VARCHAR(255) NOT NULL,
          short_description LONGTEXT NOT NULL,
          description LONGTEXT NOT NULL,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_F3E0CCBBD17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE commitment');
    }
}
