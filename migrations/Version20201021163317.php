<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201021163317 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_membership_log CHANGE description description VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE political_committee_quality CHANGE joined_at joined_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE political_committee_membership CHANGE joined_at joined_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE territorial_council_quality CHANGE joined_at joined_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE territorial_council_membership CHANGE joined_at joined_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE political_committee_membership CHANGE joined_at joined_at DATE NOT NULL');
        $this->addSql('ALTER TABLE political_committee_quality CHANGE joined_at joined_at DATE NOT NULL');
        $this->addSql('ALTER TABLE territorial_council_membership CHANGE joined_at joined_at DATE NOT NULL');
        $this->addSql('ALTER TABLE territorial_council_membership_log CHANGE description description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE territorial_council_quality CHANGE joined_at joined_at DATE NOT NULL');
    }
}
