<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180422142743 extends AbstractMigration
{
    public function up(Schema $schema)
    {
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
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490EED1A100B');
        $this->addSql('DROP INDEX IDX_E7A6490EED1A100B ON committees_memberships');
        $this->addSql('DROP INDEX adherent_has_joined_committee ON committees_memberships');
        $this->addSql('ALTER TABLE committees_memberships ADD committee_uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', CHANGE adherent_id adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(
            'UPDATE committees_memberships
            JOIN committees ON committees.id = committees_memberships.committee_id
            SET committees_memberships.committee_uuid = committees.uuid'
        );
        $this->addSql('ALTER TABLE committees_memberships DROP committee_id');
        $this->addSql('CREATE UNIQUE INDEX adherent_has_joined_committee ON committees_memberships (adherent_id, committee_uuid)');
    }
}
