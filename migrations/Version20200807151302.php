<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200807151302 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE territorial_council_candidacy_invitation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          membership_id INT UNSIGNED NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          accepted_at DATETIME DEFAULT NULL, 
          declined_at DATETIME DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_DA86009A1FB354CD (membership_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territorial_council_candidacy (
          id INT AUTO_INCREMENT NOT NULL, 
          election_id INT UNSIGNED NOT NULL, 
          membership_id INT UNSIGNED NOT NULL, 
          invitation_id INT UNSIGNED DEFAULT NULL, 
          gender VARCHAR(255) NOT NULL, 
          biography LONGTEXT DEFAULT NULL, 
          faith_statement LONGTEXT DEFAULT NULL, 
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL, 
          quality VARCHAR(255) DEFAULT NULL, 
          status VARCHAR(255) NOT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          image_name VARCHAR(255) DEFAULT NULL, 
          UNIQUE INDEX UNIQ_39885B6D17F50A6 (uuid), 
          INDEX IDX_39885B6A708DAFF (election_id), 
          INDEX IDX_39885B61FB354CD (membership_id), 
          UNIQUE INDEX UNIQ_39885B6A35D7AF0 (invitation_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy_invitation 
        ADD 
          CONSTRAINT FK_DA86009A1FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6A708DAFF FOREIGN KEY (election_id) REFERENCES territorial_council_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B61FB354CD FOREIGN KEY (membership_id) REFERENCES territorial_council_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          territorial_council_candidacy 
        ADD 
          CONSTRAINT FK_39885B6A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES territorial_council_candidacy_invitation (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A04454D17F50A6 ON committee_candidacy (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE territorial_council_candidacy DROP FOREIGN KEY FK_39885B6A35D7AF0');
        $this->addSql('DROP TABLE territorial_council_candidacy_invitation');
        $this->addSql('DROP TABLE territorial_council_candidacy');
        $this->addSql('DROP INDEX UNIQ_9A04454D17F50A6 ON committee_candidacy');
    }
}
