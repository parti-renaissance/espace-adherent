<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240918194959 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        ADD
          party_membership VARCHAR(255) DEFAULT \'exclusive\' NOT NULL');

        $this->addSql("UPDATE adherents SET party_membership = 'territoires_progres' WHERE territoire_progres_membership = 1");
        $this->addSql("UPDATE adherents SET party_membership = 'agir' WHERE agir_membership = 1");
        $this->addSql("UPDATE adherents SET party_membership = 'modem' WHERE modem_membership = 1");
        $this->addSql("UPDATE adherents SET party_membership = 'other' WHERE other_party_membership = 1");

        $this->addSql('ALTER TABLE
          adherents
        DROP
          exclusive_membership,
        DROP
          territoire_progres_membership,
        DROP
          agir_membership,
        DROP
          other_party_membership,
        DROP
          modem_membership');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        ADD
          exclusive_membership TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          territoire_progres_membership TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          agir_membership TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          other_party_membership TINYINT(1) DEFAULT 0 NOT NULL,
        ADD
          modem_membership TINYINT(1) DEFAULT 0 NOT NULL,
        DROP
          party_membership');
    }
}
