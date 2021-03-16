<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201015113728 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_feed_item ADD is_locked TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE political_committee_feed_item ADD is_locked TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE political_committee_feed_item DROP is_locked');
        $this->addSql('ALTER TABLE territorial_council_feed_item DROP is_locked');
    }
}
