<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180419091500 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE republican_silence (id INT UNSIGNED AUTO_INCREMENT NOT NULL, zones JSON NOT NULL COMMENT \'(DC2Type:json_array)\', begin_at DATETIME NOT NULL, finish_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        $this->addSql('DROP INDEX adherent_has_joined_committee ON committees_memberships');
        $this->addSql('ALTER TABLE committees_memberships ADD committee_id INT UNSIGNED NOT NULL, CHANGE adherent_id adherent_id INT UNSIGNED NOT NULL');
        $this->addSql(
            'UPDATE committees_memberships
            JOIN committees ON committees.uuid = committees_memberships.committee_uuid
            SET committees_memberships.committee_id = committees.id'
        );
        $this->addSql('ALTER TABLE committees_memberships DROP committee_uuid');
        $this->addSql('ALTER TABLE committees_memberships ADD CONSTRAINT FK_E7A6490EED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('CREATE INDEX IDX_E7A6490EED1A100B ON committees_memberships (committee_id)');
        $this->addSql('CREATE UNIQUE INDEX adherent_has_joined_committee ON committees_memberships (adherent_id, committee_id)');

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
        $this->addSql('DROP TABLE republican_silence');

        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490EED1A100B');
        $this->addSql('DROP INDEX IDX_E7A6490EED1A100B ON committees_memberships');
        $this->addSql('DROP INDEX adherent_has_joined_committee ON committees_memberships');
        $this->addSql('ALTER TABLE committees_memberships ADD committee_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', DROP committee_id, CHANGE adherent_id adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX adherent_has_joined_committee ON committees_memberships (adherent_id, committee_uuid)');

        $this->addSql('ALTER TABLE citizen_project_memberships DROP FOREIGN KEY FK_2E41816B3584533');
        $this->addSql('DROP INDEX IDX_2E41816B3584533 ON citizen_project_memberships');
        $this->addSql('DROP INDEX adherent_has_joined_citizen_project ON citizen_project_memberships');
        $this->addSql('ALTER TABLE citizen_project_memberships ADD citizen_project_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', DROP citizen_project_id, CHANGE adherent_id adherent_id INT UNSIGNED DEFAULT NULL');
    }
}
