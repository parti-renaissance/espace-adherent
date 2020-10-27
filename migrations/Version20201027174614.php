<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201027174614 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE committee_candidacy_invitation (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          membership_id INT UNSIGNED NOT NULL, 
          status VARCHAR(255) NOT NULL, 
          accepted_at DATETIME DEFAULT NULL, 
          declined_at DATETIME DEFAULT NULL, 
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_368B01611FB354CD (membership_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          committee_candidacy_invitation 
        ADD 
          CONSTRAINT FK_368B01611FB354CD FOREIGN KEY (membership_id) REFERENCES committees_memberships (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          invitation_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          binome_id INT DEFAULT NULL, 
        ADD 
          status VARCHAR(255) DEFAULT NULL, 
        ADD 
          type VARCHAR(255) DEFAULT NULL, 
        ADD 
          faith_statement LONGTEXT DEFAULT NULL, 
        ADD 
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql("UPDATE committee_candidacy SET `type` = 'committee_adherent', status = 'confirmed' WHERE `type` IS NULL");

        $this->addSql('ALTER TABLE 
          committee_candidacy 
        CHANGE status status VARCHAR(255) NOT NULL, 
        CHANGE type type VARCHAR(255) NOT NULL');

        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A04454A35D7AF0 FOREIGN KEY (invitation_id) REFERENCES committee_candidacy_invitation (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        ADD 
          CONSTRAINT FK_9A044548D4924C4 FOREIGN KEY (binome_id) REFERENCES committee_candidacy (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A04454A35D7AF0 ON committee_candidacy (invitation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9A044548D4924C4 ON committee_candidacy (binome_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A04454A35D7AF0');
        $this->addSql('DROP TABLE committee_candidacy_invitation');
        $this->addSql('ALTER TABLE committee_candidacy DROP FOREIGN KEY FK_9A044548D4924C4');
        $this->addSql('DROP INDEX UNIQ_9A04454A35D7AF0 ON committee_candidacy');
        $this->addSql('DROP INDEX UNIQ_9A044548D4924C4 ON committee_candidacy');
        $this->addSql('ALTER TABLE 
          committee_candidacy 
        DROP 
          invitation_id, 
        DROP 
          binome_id, 
        DROP 
          status, 
        DROP 
          type, 
        DROP 
          faith_statement, 
        DROP 
          is_public_faith_statement');
    }
}
