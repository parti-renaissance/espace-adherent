<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200918105807 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE thematic_community_membership_user_list_definition (
          thematic_community_membership_id INT UNSIGNED NOT NULL, 
          user_list_definition_id INT UNSIGNED NOT NULL, 
          INDEX IDX_58815EB9403AE2A5 (
            thematic_community_membership_id
          ), 
          INDEX IDX_58815EB9F74563E3 (user_list_definition_id), 
          PRIMARY KEY(
            thematic_community_membership_id, 
            user_list_definition_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          thematic_community_membership_user_list_definition 
        ADD 
          CONSTRAINT FK_58815EB9403AE2A5 FOREIGN KEY (
            thematic_community_membership_id
          ) REFERENCES thematic_community_membership (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          thematic_community_membership_user_list_definition 
        ADD 
          CONSTRAINT FK_58815EB9F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC05D38DA5D3');
        $this->addSql('DROP INDEX IDX_22B6AC05D38DA5D3 ON thematic_community_membership');
        $this->addSql('ALTER TABLE thematic_community_membership DROP elected_representative_id, DROP categories');
        $this->addSql('ALTER TABLE 
          thematic_community_contact 
        ADD 
          gender VARCHAR(255) DEFAULT NULL, 
        ADD 
          custom_gender VARCHAR(255) DEFAULT NULL, 
          CHANGE birth_date birth_date DATE DEFAULT NULL, 
          CHANGE phone phone VARCHAR(35) DEFAULT NULL COMMENT \'(DC2Type:phone_number)\', 
          CHANGE activity_area activity_area VARCHAR(255) DEFAULT NULL, 
          CHANGE job_area job_area VARCHAR(255) DEFAULT NULL, 
          CHANGE job job VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE thematic_community_membership_user_list_definition');
        $this->addSql('ALTER TABLE 
          thematic_community_contact 
        DROP 
          gender, 
        DROP 
          custom_gender, 
          CHANGE birth_date birth_date DATE NOT NULL, 
          CHANGE phone phone VARCHAR(35) NOT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:phone_number)\', 
          CHANGE activity_area activity_area VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, 
          CHANGE job_area job_area VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, 
          CHANGE job job VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          elected_representative_id INT DEFAULT NULL, 
        ADD 
          categories LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE 
          thematic_community_membership 
        ADD 
          CONSTRAINT FK_22B6AC05D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_22B6AC05D38DA5D3 ON thematic_community_membership (elected_representative_id)');
    }
}
