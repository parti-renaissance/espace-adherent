<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240115220546 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_request ADD address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE committees ADD address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations ADD address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE election_vote_place ADD address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          territorial_council_convocation
        ADD
          address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          territorial_council_election
        ADD
          address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          thematic_community_contact
        ADD
          address_additional_address VARCHAR(150) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_request DROP address_additional_address');
        $this->addSql('ALTER TABLE adherents DROP address_additional_address');
        $this->addSql('ALTER TABLE committees DROP address_additional_address');
        $this->addSql('ALTER TABLE contact DROP address_additional_address');
        $this->addSql('ALTER TABLE donations DROP address_additional_address');
        $this->addSql('ALTER TABLE election_vote_place DROP address_additional_address');
        $this->addSql('ALTER TABLE events DROP address_additional_address');
        $this->addSql('ALTER TABLE territorial_council_convocation DROP address_additional_address');
        $this->addSql('ALTER TABLE territorial_council_election DROP address_additional_address');
        $this->addSql('ALTER TABLE thematic_community_contact DROP address_additional_address');
    }
}
