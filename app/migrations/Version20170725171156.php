<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170725171156 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE skills (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX skill_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE summary_skills (summary_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_2FD2B63C2AC2D45C (summary_id), INDEX IDX_2FD2B63C5585C142 (skill_id), PRIMARY KEY(summary_id, skill_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE summary_skills ADD CONSTRAINT FK_2FD2B63C2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('ALTER TABLE summary_skills ADD CONSTRAINT FK_2FD2B63C5585C142 FOREIGN KEY (skill_id) REFERENCES skills (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE summary_skills DROP FOREIGN KEY FK_2FD2B63C5585C142');
        $this->addSql('CREATE TABLE member_summary_skills (id INT AUTO_INCREMENT NOT NULL, summary_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_CB3F6F8F2AC2D45C (summary_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE member_summary_skills ADD CONSTRAINT FK_CB3F6F8F2AC2D45C FOREIGN KEY (summary_id) REFERENCES summaries (id)');
        $this->addSql('DROP TABLE skills');
    }
}
