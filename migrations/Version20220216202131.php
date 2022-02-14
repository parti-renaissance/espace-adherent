<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220216202131 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE header_block (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(100) NOT NULL,
          slug VARCHAR(130) NOT NULL,
          prefix VARCHAR(50) NOT NULL,
          slogan VARCHAR(100) DEFAULT NULL,
          content LONGTEXT DEFAULT NULL,
          deadline_date DATETIME DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_10BFECC55E237E06 (name),
          UNIQUE INDEX UNIQ_10BFECC5989D9B62 (slug),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE header_block');
    }
}
