<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180522140022 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('DROP INDEX adherent_has_joined_citizen_project ON citizen_project_memberships');
        $this->addSql('ALTER TABLE citizen_project_memberships ADD citizen_project_id INT UNSIGNED NOT NULL, CHANGE adherent_id adherent_id INT UNSIGNED NOT NULL');
        $this->addSql(
            'UPDATE citizen_project_memberships
            JOIN citizen_projects ON citizen_projects.uuid = citizen_project_memberships.citizen_project_uuid
            SET citizen_project_memberships.citizen_project_id = citizen_projects.id'
        );
        $this->addSql('ALTER TABLE citizen_project_memberships DROP citizen_project_uuid');
        $this->addSql('ALTER TABLE citizen_project_memberships ADD CONSTRAINT FK_2E41816B3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id)');
        $this->addSql('CREATE INDEX IDX_2E41816B3584533 ON citizen_project_memberships (citizen_project_id)');
        $this->addSql('CREATE UNIQUE INDEX adherent_has_joined_citizen_project ON citizen_project_memberships (adherent_id, citizen_project_id)');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE citizen_project_memberships DROP FOREIGN KEY FK_2E41816B3584533');
        $this->addSql('DROP INDEX IDX_2E41816B3584533 ON citizen_project_memberships');
        $this->addSql('DROP INDEX adherent_has_joined_citizen_project ON citizen_project_memberships');
        $this->addSql('ALTER TABLE citizen_project_memberships ADD citizen_project_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE adherent_id adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(
            'UPDATE citizen_project_memberships
            JOIN citizen_projects ON citizen_projects.id = citizen_project_memberships.citizen_project_id
            SET citizen_project_memberships.citizen_project_uuid = citizen_projects.uuid'
        );
        $this->addSql('ALTER TABLE citizen_project_memberships DROP citizen_project_id');
        $this->addSql('CREATE UNIQUE INDEX adherent_has_joined_citizen_project ON citizen_project_memberships (adherent_id, citizen_project_uuid)');
    }
}
