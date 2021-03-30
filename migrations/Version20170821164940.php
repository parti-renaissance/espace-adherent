<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20170821164940 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE member_summary_skills');
        $this->addSql('ALTER TABLE events ADD expert_found TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE member_summary_skills (id INT AUTO_INCREMENT NOT NULL, summary_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_CB3F6F8F2AC2D45C (summary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE member_summary_skills ADD CONSTRAINT FK_CB3F6F8F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE events DROP expert_found');
    }
}
