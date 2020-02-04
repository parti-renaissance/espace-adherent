<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200204165842 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donators ADD nationality VARCHAR(2) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE donators AS donator
            INNER JOIN donations AS donation
                ON donation.donator_id = donator.id
            SET donator.nationality = donation.nationality
SQL
        );
        $this->addSql('ALTER TABLE donations ADD beneficiary VARCHAR(255) DEFAULT \'AFEMA\' NOT NULL, DROP nationality');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations ADD nationality VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci, DROP beneficiary');
        $this->addSql(<<<'SQL'
            UPDATE donations AS donation
            INNER JOIN donators AS donator
                ON donation.donator_id = donator.id
            SET donation.nationality = donator.nationality
SQL
        );
        $this->addSql('ALTER TABLE donators DROP nationality');
    }
}
