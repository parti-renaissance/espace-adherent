<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180117113145 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events DROP FOREIGN KEY FK_5387574AE03E2EB9');
        $this->addSql('DROP TABLE citizen_initiative_categories');
        $this->addSql('DROP TABLE citizen_initiative_skills');
        $this->addSql('DROP INDEX IDX_5387574AE03E2EB9 ON events');
        $this->addSql('ALTER TABLE events DROP citizen_initiative_category_id, DROP interests, DROP expert_assistance_needed, DROP coaching_requested, DROP coaching_request_problem_description, DROP coaching_request_proposed_solution, DROP coaching_request_required_means, DROP expert_found, DROP was_published, DROP place');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id)');
        $this->addSql('DELETE FROM events_registrations WHERE event_id IN (SELECT id FROM events WHERE type=\'citizen_initiative\')');
        $this->addSql('DELETE FROM events_invitations WHERE event_id IN (SELECT id FROM events WHERE type=\'citizen_initiative\')');
        $this->addSql('DELETE FROM events WHERE type=\'citizen_initiative\'');
    }

    public function down(Schema $schema)
    {
        $this->addSql('CREATE TABLE citizen_initiative_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL COLLATE utf8_unicode_ci, status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX citizen_initiative_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_initiative_skills (citizen_initiative_id INT UNSIGNED NOT NULL, skill_id INT NOT NULL, INDEX IDX_F936A5506FBEFC74 (citizen_initiative_id), INDEX IDX_F936A5505585C142 (skill_id), PRIMARY KEY(citizen_initiative_id, skill_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_initiative_skills ADD CONSTRAINT FK_F936A5505585C142 FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_initiative_skills ADD CONSTRAINT FK_F936A5506FBEFC74 FOREIGN KEY (citizen_initiative_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9B3584533');
        $this->addSql('ALTER TABLE citizen_projects_skills DROP FOREIGN KEY FK_B3D202D9EA64A9D0');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_projects_skills ADD CONSTRAINT FK_B3D202D9EA64A9D0 FOREIGN KEY (citizen_project_skill_id) REFERENCES citizen_project_skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events ADD citizen_initiative_category_id INT UNSIGNED DEFAULT NULL, ADD interests JSON DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\', ADD expert_assistance_needed TINYINT(1) DEFAULT \'0\', ADD coaching_requested TINYINT(1) DEFAULT \'0\', ADD coaching_request_problem_description VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD coaching_request_proposed_solution VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD coaching_request_required_means VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD expert_found TINYINT(1) DEFAULT \'0\' NOT NULL, ADD was_published TINYINT(1) DEFAULT \'0\', ADD place VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE events ADD CONSTRAINT FK_5387574AE03E2EB9 FOREIGN KEY (citizen_initiative_category_id) REFERENCES citizen_initiative_categories (id)');
        $this->addSql('CREATE INDEX IDX_5387574AE03E2EB9 ON events (citizen_initiative_category_id)');
    }
}
