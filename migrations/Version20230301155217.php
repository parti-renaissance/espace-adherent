<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230301155217 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacy_invitation DROP FOREIGN KEY FK_368B016159B22434');
        $this->addSql('ALTER TABLE territorial_council_candidacy_invitation DROP FOREIGN KEY FK_DA86009A59B22434');
        $this->addSql('ALTER TABLE
          committee_candidacies_group
        ADD
          election_id INT UNSIGNED DEFAULT NULL,
        ADD
          uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          created_at DATETIME DEFAULT NOW(),
        ADD
          updated_at DATETIME DEFAULT NOW()');
        $this->addSql('ALTER TABLE
          committee_candidacies_group
        ADD
          CONSTRAINT FK_AF772F42A708DAFF FOREIGN KEY (election_id) REFERENCES committee_election (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF772F42D17F50A6 ON committee_candidacies_group (uuid)');
        $this->addSql('UPDATE committee_candidacies_group SET uuid = UUID()');
        $this->addSql('CREATE INDEX IDX_AF772F42A708DAFF ON committee_candidacies_group (election_id)');
        $this->addSql('ALTER TABLE
          committee_candidacies_group
        CHANGE uuid uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
                CHANGE
          created_at created_at DATETIME NOT NULL,
        CHANGE
          updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE committee_candidacy CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          committee_candidacy_invitation
        CHANGE
          candidacy_id candidacy_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE local_election_candidacy CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          local_election_substitute_candidacy
        CHANGE
          id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE national_council_candidacy CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE territorial_council_candidacy CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        CHANGE
          candidacy_id candidacy_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          committee_candidacy_invitation
        ADD
          CONSTRAINT FK_368B016159B22434 FOREIGN KEY (candidacy_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        ADD
          CONSTRAINT FK_DA86009A59B22434 FOREIGN KEY (candidacy_id) REFERENCES territorial_council_candidacy (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacies_group DROP FOREIGN KEY FK_AF772F42A708DAFF');
        $this->addSql('DROP INDEX UNIQ_AF772F42D17F50A6 ON committee_candidacies_group');
        $this->addSql('DROP INDEX IDX_AF772F42A708DAFF ON committee_candidacies_group');
        $this->addSql('ALTER TABLE committee_candidacies_group DROP election_id, DROP uuid');
        $this->addSql('ALTER TABLE committee_candidacy CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE committee_candidacy_invitation CHANGE candidacy_id candidacy_id INT NOT NULL');
        $this->addSql('ALTER TABLE local_election_candidacy CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE local_election_substitute_candidacy CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE national_council_candidacy CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE territorial_council_candidacy CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE
          territorial_council_candidacy_invitation
        CHANGE
          candidacy_id candidacy_id INT NOT NULL');
    }
}
