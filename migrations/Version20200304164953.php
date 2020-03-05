<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200304164953 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE committee_candidacy (
          id INT AUTO_INCREMENT NOT NULL,
          committee_election_id INT NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_9A044544E891720 (committee_election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE committee_election (
          id INT AUTO_INCREMENT NOT NULL,
          committee_id INT UNSIGNED NOT NULL,
          UNIQUE INDEX UNIQ_2CA406E5ED1A100B (committee_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          committee_candidacy
        ADD
          CONSTRAINT FK_9A044544E891720 FOREIGN KEY (committee_election_id) REFERENCES committee_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          committee_election
        ADD
          CONSTRAINT FK_2CA406E5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
        $this->addSql('ALTER TABLE committees_memberships ADD committee_candidacy_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          committees_memberships
        ADD
          CONSTRAINT FK_E7A6490E4F376ABC FOREIGN KEY (committee_candidacy_id) REFERENCES committee_candidacy (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E7A6490E4F376ABC ON committees_memberships (committee_candidacy_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees_memberships DROP FOREIGN KEY FK_E7A6490E4F376ABC');
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A044544E891720');
        $this->addSql('DROP TABLE committee_candidacy');
        $this->addSql('DROP TABLE committee_election');
        $this->addSql('DROP INDEX UNIQ_E7A6490E4F376ABC ON committees_memberships');
        $this->addSql('ALTER TABLE committees_memberships DROP committee_candidacy_id');
    }
}
