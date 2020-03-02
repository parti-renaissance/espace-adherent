<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200224124920 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees_memberships ADD enable_vote TINYINT(1) NULL');
        $this->addSql('CREATE UNIQUE INDEX adherent_votes_in_committee ON committees_memberships (
          adherent_id, enable_vote
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees_memberships DROP enable_vote');
        $this->addSql('DROP INDEX adherent_votes_in_committee ON committees_memberships');
    }
}
