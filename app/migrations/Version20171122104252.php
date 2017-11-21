<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171122104252 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE citizen_project_skills (citizen_initiative_id INT UNSIGNED NOT NULL, skill_id INT NOT NULL, INDEX IDX_B23BCADE6FBEFC74 (citizen_initiative_id), INDEX IDX_B23BCADE5585C142 (skill_id), PRIMARY KEY(citizen_initiative_id, skill_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_project_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, UNIQUE INDEX citizen_project_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_skills ADD CONSTRAINT FK_B23BCADE6FBEFC74 FOREIGN KEY (citizen_initiative_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_project_skills ADD CONSTRAINT FK_B23BCADE5585C142 FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_projects ADD category_id INT UNSIGNED DEFAULT NULL, ADD subtitle VARCHAR(255) NOT NULL, ADD assistance_needed TINYINT(1) DEFAULT \'0\' NOT NULL, ADD problem_description VARCHAR(255) DEFAULT NULL, ADD proposed_solution VARCHAR(255) DEFAULT NULL, ADD required_means VARCHAR(255) DEFAULT NULL, DROP description, DROP phone');
        $this->addSql('ALTER TABLE citizen_projects ADD CONSTRAINT FK_651490212469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
        $this->addSql('CREATE INDEX IDX_651490212469DE2 ON citizen_projects (category_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_651490212469DE2');
        $this->addSql('DROP TABLE citizen_project_skills');
        $this->addSql('DROP TABLE citizen_project_categories');
        $this->addSql('DROP INDEX IDX_651490212469DE2 ON citizen_projects');
        $this->addSql('ALTER TABLE citizen_projects DROP category_id, DROP subtitle, DROP assistance_needed, DROP problem_description, DROP proposed_solution, DROP required_means, ADD description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, ADD phone VARCHAR(35) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:phone_number)\'');
    }
}
