<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220119094725 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        CHANGE
          nb_addresses nb_addresses INT UNSIGNED DEFAULT 0 NOT NULL,
        CHANGE
          nb_voters nb_voters INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          pap_campaign
        CHANGE
          nb_addresses nb_addresses SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
        CHANGE
          nb_voters nb_voters SMALLINT UNSIGNED DEFAULT 0 NOT NULL');
    }
}
