<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171129011217 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE citizen_project_categories (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, status VARCHAR(10) DEFAULT \'ENABLED\' NOT NULL, UNIQUE INDEX citizen_project_category_name_unique (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_projects ADD category_id INT UNSIGNED DEFAULT NULL, ADD committee_id INT UNSIGNED DEFAULT NULL, ADD subtitle VARCHAR(255) NOT NULL, ADD problem_description LONGTEXT DEFAULT NULL, ADD proposed_solution LONGTEXT DEFAULT NULL, ADD required_means LONGTEXT DEFAULT NULL, ADD assistance_needed TINYINT(1) DEFAULT \'0\' NOT NULL, ADD assistance_content VARCHAR(255) DEFAULT NULL, DROP description');
        $this->addSql('ALTER TABLE citizen_projects ADD CONSTRAINT FK_651490212469DE2 FOREIGN KEY (category_id) REFERENCES citizen_project_categories (id)');
        $this->addSql('ALTER TABLE citizen_projects ADD CONSTRAINT FK_6514902ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('CREATE INDEX IDX_651490212469DE2 ON citizen_projects (category_id)');
        $this->addSql('CREATE INDEX IDX_6514902ED1A100B ON citizen_projects (committee_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_651490212469DE2');
        $this->addSql('DROP TABLE citizen_project_categories');
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_6514902ED1A100B');
        $this->addSql('DROP INDEX IDX_651490212469DE2 ON citizen_projects');
        $this->addSql('DROP INDEX IDX_6514902ED1A100B ON citizen_projects');
        $this->addSql('ALTER TABLE citizen_projects ADD description LONGTEXT NOT NULL COLLATE utf8_unicode_ci, DROP category_id, DROP committee_id, DROP subtitle, DROP problem_description, DROP proposed_solution, DROP required_means, DROP assistance_needed, DROP assistance_content');
    }
}
