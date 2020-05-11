<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200507124731 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_team_member_committee (
          referent_team_member_id INT NOT NULL, 
          committee_id INT UNSIGNED NOT NULL, 
          INDEX IDX_EC89860BFE4CA267 (referent_team_member_id), 
          INDEX IDX_EC89860BED1A100B (committee_id), 
          PRIMARY KEY(
            referent_team_member_id, committee_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent_person_link_committee (
          referent_person_link_id INT UNSIGNED NOT NULL, 
          committee_id INT UNSIGNED NOT NULL, 
          INDEX IDX_1C97B2A5B3E4DE86 (referent_person_link_id), 
          INDEX IDX_1C97B2A5ED1A100B (committee_id), 
          PRIMARY KEY(
            referent_person_link_id, committee_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          referent_team_member_committee 
        ADD 
          CONSTRAINT FK_EC89860BFE4CA267 FOREIGN KEY (referent_team_member_id) REFERENCES referent_team_member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_team_member_committee 
        ADD 
          CONSTRAINT FK_EC89860BED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_person_link_committee 
        ADD 
          CONSTRAINT FK_1C97B2A5B3E4DE86 FOREIGN KEY (referent_person_link_id) REFERENCES referent_person_link (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_person_link_committee 
        ADD 
          CONSTRAINT FK_1C97B2A5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          referent_team_member 
        ADD 
          restricted_cities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE 
          referent_person_link 
        ADD 
          restricted_cities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE referent_team_member_committee');
        $this->addSql('DROP TABLE referent_person_link_committee');
        $this->addSql('ALTER TABLE referent_person_link DROP restricted_cities');
        $this->addSql('ALTER TABLE referent_team_member DROP restricted_cities');
    }
}
