<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220401171433 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_vote_place
        ADD
          nb_addresses INT UNSIGNED DEFAULT 0 NOT NULL,
        ADD
          nb_voters INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_vote_place DROP nb_addresses, DROP nb_voters');
    }
}
