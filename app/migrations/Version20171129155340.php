<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171129155340 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE citizen_projects_skills (citizen_project_id INT UNSIGNED NOT NULL, citizen_project_skill_id INT NOT NULL, INDEX IDX_B3D202D9B3584533 (citizen_project_id), INDEX IDX_B3D202D9EA64A9D0 (citizen_project_skill_id), PRIMARY KEY(citizen_project_id, citizen_project_skill_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE citizen_projects_skills');
        $this->addSql('ALTER TABLE citizen_projects CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
