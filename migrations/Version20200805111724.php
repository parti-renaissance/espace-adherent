<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200805111724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE designation_referent_tag (
          designation_id INT UNSIGNED NOT NULL, 
          referent_tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_7538F35AFAC7D83F (designation_id), 
          INDEX IDX_7538F35A9C262DB3 (referent_tag_id), 
          PRIMARY KEY(designation_id, referent_tag_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE territorial_council_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          territorial_council_id INT UNSIGNED DEFAULT NULL, 
          designation_id INT UNSIGNED DEFAULT NULL, 
          INDEX IDX_14CBC36BAAA61A99 (territorial_council_id), 
          INDEX IDX_14CBC36BFAC7D83F (designation_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          designation_referent_tag 
        ADD 
          CONSTRAINT FK_7538F35AFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          designation_referent_tag 
        ADD 
          CONSTRAINT FK_7538F35A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          CONSTRAINT FK_14CBC36BAAA61A99 FOREIGN KEY (territorial_council_id) REFERENCES territorial_council (id)');
        $this->addSql('ALTER TABLE 
          territorial_council_election 
        ADD 
          CONSTRAINT FK_14CBC36BFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE 
          designation CHANGE zones zones LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE territorial_council ADD current_designation_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          territorial_council 
        ADD 
          CONSTRAINT FK_B6DCA2A5B4D2A5D1 FOREIGN KEY (current_designation_id) REFERENCES designation (id)');
        $this->addSql('CREATE INDEX IDX_B6DCA2A5B4D2A5D1 ON territorial_council (current_designation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE designation_referent_tag');
        $this->addSql('DROP TABLE territorial_council_election');
        $this->addSql('ALTER TABLE 
          designation CHANGE zones zones LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE territorial_council DROP FOREIGN KEY FK_B6DCA2A5B4D2A5D1');
        $this->addSql('DROP INDEX IDX_B6DCA2A5B4D2A5D1 ON territorial_council');
        $this->addSql('ALTER TABLE territorial_council DROP current_designation_id');
    }
}
