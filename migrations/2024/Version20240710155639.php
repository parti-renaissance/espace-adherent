<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240710155639 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_thematic_community DROP FOREIGN KEY FK_DAB0B4EC1BE5825E');
        $this->addSql('ALTER TABLE adherent_thematic_community DROP FOREIGN KEY FK_DAB0B4EC25F06C53');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC0525F06C53');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC05E7A1254A');
        $this->addSql('ALTER TABLE thematic_community_membership DROP FOREIGN KEY FK_22B6AC05FDA7B0BF');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        DROP
          FOREIGN KEY FK_58815EB9403AE2A5');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        DROP
          FOREIGN KEY FK_58815EB9F74563E3');
        $this->addSql('DROP TABLE adherent_thematic_community');
        $this->addSql('DROP TABLE thematic_community');
        $this->addSql('DROP TABLE thematic_community_contact');
        $this->addSql('DROP TABLE thematic_community_membership');
        $this->addSql('DROP TABLE thematic_community_membership_user_list_definition');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_thematic_community (
          adherent_id INT UNSIGNED NOT NULL,
          thematic_community_id INT UNSIGNED NOT NULL,
          INDEX IDX_DAB0B4EC1BE5825E (thematic_community_id),
          INDEX IDX_DAB0B4EC25F06C53 (adherent_id),
          PRIMARY KEY(
            adherent_id, thematic_community_id
          )
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE thematic_community (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          enabled TINYINT(1) NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          canonical_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_6F22A458D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE thematic_community_contact (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          first_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          birth_date DATE DEFAULT NULL,
          phone VARCHAR(35) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:phone_number)\',
          activity_area VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          job_area VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          job VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          address_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_postal_code VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_insee VARCHAR(15) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_city_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          address_region VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_geocodable_hash VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          custom_gender VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          position VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          address_additional_address VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_5C0B5CEAD17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('CREATE TABLE thematic_community_membership (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          community_id INT UNSIGNED DEFAULT NULL,
          contact_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          joined_at DATETIME NOT NULL,
          association TINYINT(1) DEFAULT 0 NOT NULL,
          association_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          expert TINYINT(1) DEFAULT 0 NOT NULL,
          uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
          TYPE VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          has_job TINYINT(1) DEFAULT 0 NOT NULL,
          job VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          motivations LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\',
          STATUS VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_22B6AC05D17F50A6 (uuid),
          INDEX IDX_22B6AC0525F06C53 (adherent_id),
          INDEX IDX_22B6AC05E7A1254A (contact_id),
          INDEX IDX_22B6AC05FDA7B0BF (community_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          adherent_thematic_community
        ADD
          CONSTRAINT FK_DAB0B4EC1BE5825E FOREIGN KEY (thematic_community_id) REFERENCES thematic_community (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          adherent_thematic_community
        ADD
          CONSTRAINT FK_DAB0B4EC25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership
        ADD
          CONSTRAINT FK_22B6AC0525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership
        ADD
          CONSTRAINT FK_22B6AC05E7A1254A FOREIGN KEY (contact_id) REFERENCES thematic_community_contact (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership
        ADD
          CONSTRAINT FK_22B6AC05FDA7B0BF FOREIGN KEY (community_id) REFERENCES thematic_community (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        ADD
          CONSTRAINT FK_58815EB9403AE2A5 FOREIGN KEY (
            thematic_community_membership_id
          ) REFERENCES thematic_community_membership (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          thematic_community_membership_user_list_definition
        ADD
          CONSTRAINT FK_58815EB9F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
