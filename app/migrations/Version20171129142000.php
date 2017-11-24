<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171129142000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE citizen_project_category_skills (id INT AUTO_INCREMENT NOT NULL, category_id INT UNSIGNED DEFAULT NULL, skill_id INT DEFAULT NULL, promotion TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_168C868A12469DE2 (category_id), INDEX IDX_168C868A5585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_project_skills (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX citizen_project_skill_slug_unique (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_category_skills ADD CONSTRAINT FK_168C868A12469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
        $this->addSql('ALTER TABLE citizen_project_category_skills ADD CONSTRAINT FK_168C868A5585C142 FOREIGN KEY (skill_id) REFERENCES citizen_project_skills (id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE citizen_project_category_skills DROP FOREIGN KEY FK_168C868A5585C142');
        $this->addSql('DROP TABLE citizen_project_category_skills');
        $this->addSql('DROP TABLE citizen_project_skills');
    }
}
