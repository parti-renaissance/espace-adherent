<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210615134639 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notification (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          notification_class VARCHAR(255) NOT NULL,
          title VARCHAR(255) NOT NULL,
          body LONGTEXT NOT NULL,
          delivered_at DATETIME DEFAULT NULL,
          topic VARCHAR(255) DEFAULT NULL,
          tokens LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE events ADD reminded TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE events DROP reminded');
    }
}
