<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20180131021236 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950adc14e7bc9 TO IDX_A956D4E4C14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950ad6bf91e2f TO IDX_A956D4E46BF91E2F');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id)');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e4c14e7bc9 TO IDX_69D950ADC14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e46bf91e2f TO IDX_69D950AD6BF91E2F');
    }
}
