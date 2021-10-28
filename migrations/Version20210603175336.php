<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210603175336 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles DROP amp_content');
        $this->addSql('ALTER TABLE clarifications DROP amp_content');
        $this->addSql('ALTER TABLE order_articles DROP amp_content');
        $this->addSql('ALTER TABLE pages DROP amp_content');
        $this->addSql('ALTER TABLE proposals DROP amp_content');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          articles
        ADD
          amp_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          clarifications
        ADD
          amp_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          order_articles
        ADD
          amp_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          pages
        ADD
          amp_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          proposals
        ADD
          amp_content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
