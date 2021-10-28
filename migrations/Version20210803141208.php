<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210803141208 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jecoute_riposte (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          title VARCHAR(255) NOT NULL,
          body LONGTEXT NOT NULL,
          source_url VARCHAR(255) DEFAULT NULL,
          with_notification TINYINT(1) DEFAULT \'1\' NOT NULL,
          enabled TINYINT(1) DEFAULT \'1\' NOT NULL,
          created_by_id INT DEFAULT NULL,
          author_id INT UNSIGNED DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_17E1064BB03A8386 (created_by_id),
          INDEX IDX_17E1064BF675F31B (author_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          CONSTRAINT FK_17E1064BB03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jecoute_riposte
        ADD
          CONSTRAINT FK_17E1064BF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jecoute_riposte');
    }
}
