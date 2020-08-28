<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200828192709 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP FOREIGN KEY FK_BCDA0C151FB354CD');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote CHANGE membership_id membership_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote 
        ADD 
          CONSTRAINT FK_BCDA0C151FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_election_poll_vote DROP FOREIGN KEY FK_BCDA0C151FB354CD');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote CHANGE membership_id membership_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          territorial_council_election_poll_vote 
        ADD 
          CONSTRAINT FK_BCDA0C151FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id)');
    }
}
