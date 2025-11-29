<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221209162545 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          local_election_candidacies_group
        ADD
          created_by_administrator_id INT DEFAULT NULL,
        ADD
          updated_by_administrator_id INT DEFAULT NULL,
        ADD
          created_at DATETIME DEFAULT NULL,
        ADD
          updated_at DATETIME DEFAULT NULL');

        $this->addSql('UPDATE local_election_candidacies_group SET created_at = NOW(), updated_at = NOW()');

        $this->addSql('ALTER TABLE
          local_election_candidacies_group
        CHANGE
          created_at created_at DATETIME NOT NULL,
        CHANGE
          updated_at updated_at DATETIME NOT NULL');

        $this->addSql('ALTER TABLE
          local_election_candidacies_group
        ADD
          CONSTRAINT FK_8D478DE89DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          local_election_candidacies_group
        ADD
          CONSTRAINT FK_8D478DE8CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_8D478DE89DF5350C ON local_election_candidacies_group (created_by_administrator_id)');
        $this->addSql('CREATE INDEX IDX_8D478DE8CF1918FF ON local_election_candidacies_group (updated_by_administrator_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE local_election_candidacies_group DROP FOREIGN KEY FK_8D478DE89DF5350C');
        $this->addSql('ALTER TABLE local_election_candidacies_group DROP FOREIGN KEY FK_8D478DE8CF1918FF');
        $this->addSql('DROP INDEX IDX_8D478DE89DF5350C ON local_election_candidacies_group');
        $this->addSql('DROP INDEX IDX_8D478DE8CF1918FF ON local_election_candidacies_group');
        $this->addSql('ALTER TABLE
          local_election_candidacies_group
        DROP
          created_by_administrator_id,
        DROP
          updated_by_administrator_id,
        DROP
          created_at,
        DROP
          updated_at');
    }
}
