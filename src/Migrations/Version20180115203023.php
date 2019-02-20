<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180115203023 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id)');
    }
}
