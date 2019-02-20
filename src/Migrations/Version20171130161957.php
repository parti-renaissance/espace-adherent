<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20171130161957 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE citizen_project_committee_supports (id INT AUTO_INCREMENT NOT NULL, citizen_project_id INT UNSIGNED DEFAULT NULL, committee_id INT UNSIGNED DEFAULT NULL, status VARCHAR(20) NOT NULL, requested_at DATETIME DEFAULT NULL, approved_at DATETIME DEFAULT NULL, INDEX IDX_F694C3BCB3584533 (citizen_project_id), INDEX IDX_F694C3BCED1A100B (committee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_committee_supports ADD CONSTRAINT FK_F694C3BCB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('ALTER TABLE citizen_project_committee_supports ADD CONSTRAINT FK_F694C3BCED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE citizen_projects DROP FOREIGN KEY FK_6514902ED1A100B');
        $this->addSql('DROP INDEX IDX_6514902ED1A100B ON citizen_projects');
        $this->addSql('ALTER TABLE citizen_projects DROP committee_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE citizen_project_committee_supports');
        $this->addSql('ALTER TABLE citizen_projects ADD committee_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE citizen_projects ADD CONSTRAINT FK_6514902ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('CREATE INDEX IDX_6514902ED1A100B ON citizen_projects (committee_id)');
    }
}
