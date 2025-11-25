<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251125003333 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP FOREIGN KEY FK_28CA9F94F74563E3');
        $this->addSql('ALTER TABLE cities DROP FOREIGN KEY FK_D95DB16BAE80F5DF');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A98260155');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP FOREIGN KEY FK_A9C53A24D38DA5D3');
        $this->addSql('ALTER TABLE elected_representative_user_list_definition DROP FOREIGN KEY FK_A9C53A24F74563E3');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                DROP
                  FOREIGN KEY FK_1ECF756625F06C53
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                DROP
                  FOREIGN KEY FK_1ECF75664B09E92C
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                DROP
                  FOREIGN KEY FK_1ECF7566D38DA5D3
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                DROP
                  FOREIGN KEY FK_1ECF7566F74563E3
            SQL);
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP FOREIGN KEY FK_213A5F1EF0ED738A');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP FOREIGN KEY FK_F004297F5DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP FOREIGN KEY FK_F004297FBAD26311');
        $this->addSql('ALTER TABLE programmatic_foundation_project DROP FOREIGN KEY FK_8E8E96D55DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP FOREIGN KEY FK_9F63872166D1F9C');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP FOREIGN KEY FK_9F63872BAD26311');
        $this->addSql('ALTER TABLE programmatic_foundation_sub_approach DROP FOREIGN KEY FK_735C1D0115140614');
        $this->addSql('DROP TABLE cities');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE elected_representative_user_list_definition');
        $this->addSql('DROP TABLE elected_representative_user_list_definition_history');
        $this->addSql('DROP TABLE programmatic_foundation_approach');
        $this->addSql('DROP TABLE programmatic_foundation_measure');
        $this->addSql('DROP TABLE programmatic_foundation_measure_tag');
        $this->addSql('DROP TABLE programmatic_foundation_project');
        $this->addSql('DROP TABLE programmatic_foundation_project_tag');
        $this->addSql('DROP TABLE programmatic_foundation_sub_approach');
        $this->addSql('DROP TABLE programmatic_foundation_tag');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE user_list_definition');
        $this->addSql('DROP INDEX IDX_28CA9F94F74563E3 ON adherent_message_filters');
        $this->addSql('ALTER TABLE adherent_message_filters DROP user_list_definition_id');
        $this->addSql('ALTER TABLE adherents DROP election_results_reporter');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE cities (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  department_id INT UNSIGNED DEFAULT NULL,
                  name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  insee_code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  postal_codes LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:simple_array)',
                  INDEX IDX_D95DB16BAE80F5DF (department_id),
                  UNIQUE INDEX UNIQ_D95DB16B15A3C1BC (insee_code),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE department (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  region_id INT UNSIGNED NOT NULL,
                  name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  label VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  INDEX IDX_CD1DE18A98260155 (region_id),
                  UNIQUE INDEX UNIQ_CD1DE18A77153098 (code),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE elected_representative_user_list_definition (
                  elected_representative_id INT UNSIGNED NOT NULL,
                  user_list_definition_id INT UNSIGNED NOT NULL,
                  INDEX IDX_A9C53A24D38DA5D3 (elected_representative_id),
                  INDEX IDX_A9C53A24F74563E3 (user_list_definition_id),
                  PRIMARY KEY(
                    elected_representative_id, user_list_definition_id
                  )
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE elected_representative_user_list_definition_history (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  elected_representative_id INT UNSIGNED NOT NULL,
                  user_list_definition_id INT UNSIGNED NOT NULL,
                  adherent_id INT UNSIGNED DEFAULT NULL,
                  administrator_id INT DEFAULT NULL,
                  action VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                  INDEX IDX_1ECF756625F06C53 (adherent_id),
                  INDEX IDX_1ECF75664B09E92C (administrator_id),
                  INDEX IDX_1ECF7566D38DA5D3 (elected_representative_id),
                  INDEX IDX_1ECF7566F74563E3 (user_list_definition_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE programmatic_foundation_approach (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  position SMALLINT NOT NULL,
                  title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:uuid)',
                  UNIQUE INDEX UNIQ_8B785227D17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE programmatic_foundation_measure (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  sub_approach_id INT UNSIGNED DEFAULT NULL,
                  position SMALLINT NOT NULL,
                  title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  is_leading TINYINT(1) NOT NULL,
                  is_expanded TINYINT(1) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:uuid)',
                  INDEX IDX_213A5F1EF0ED738A (sub_approach_id),
                  UNIQUE INDEX UNIQ_213A5F1ED17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE programmatic_foundation_measure_tag (
                  measure_id INT UNSIGNED NOT NULL,
                  tag_id INT UNSIGNED NOT NULL,
                  INDEX IDX_F004297F5DA37D00 (measure_id),
                  INDEX IDX_F004297FBAD26311 (tag_id),
                  PRIMARY KEY(measure_id, tag_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE programmatic_foundation_project (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  measure_id INT UNSIGNED DEFAULT NULL,
                  position SMALLINT NOT NULL,
                  title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  city VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  is_expanded TINYINT(1) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:uuid)',
                  INDEX IDX_8E8E96D55DA37D00 (measure_id),
                  UNIQUE INDEX UNIQ_8E8E96D5D17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE programmatic_foundation_project_tag (
                  project_id INT UNSIGNED NOT NULL,
                  tag_id INT UNSIGNED NOT NULL,
                  INDEX IDX_9F63872166D1F9C (project_id),
                  INDEX IDX_9F63872BAD26311 (tag_id),
                  PRIMARY KEY(project_id, tag_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE programmatic_foundation_sub_approach (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  approach_id INT UNSIGNED DEFAULT NULL,
                  position SMALLINT NOT NULL,
                  title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  subtitle VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  is_expanded TINYINT(1) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  uuid CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT '(DC2Type:uuid)',
                  INDEX IDX_735C1D0115140614 (approach_id),
                  UNIQUE INDEX UNIQ_735C1D01D17F50A6 (uuid),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE programmatic_foundation_tag (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  UNIQUE INDEX UNIQ_12127927EA750E8 (label),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE region (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  code VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  country VARCHAR(2) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  UNIQUE INDEX UNIQ_F62F17677153098 (code),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE user_list_definition (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  type VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  code VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  label VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
                  color VARCHAR(7) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
                  UNIQUE INDEX user_list_definition_type_code_unique (type, code),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  cities
                ADD
                  CONSTRAINT FK_D95DB16BAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  department
                ADD
                  CONSTRAINT FK_CD1DE18A98260155 FOREIGN KEY (region_id) REFERENCES region (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition
                ADD
                  CONSTRAINT FK_A9C53A24D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition
                ADD
                  CONSTRAINT FK_A9C53A24F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                ADD
                  CONSTRAINT FK_1ECF756625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                ADD
                  CONSTRAINT FK_1ECF75664B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON
                UPDATE
                  NO ACTION ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                ADD
                  CONSTRAINT FK_1ECF7566D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  elected_representative_user_list_definition_history
                ADD
                  CONSTRAINT FK_1ECF7566F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  programmatic_foundation_measure
                ADD
                  CONSTRAINT FK_213A5F1EF0ED738A FOREIGN KEY (sub_approach_id) REFERENCES programmatic_foundation_sub_approach (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  programmatic_foundation_measure_tag
                ADD
                  CONSTRAINT FK_F004297F5DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  programmatic_foundation_measure_tag
                ADD
                  CONSTRAINT FK_F004297FBAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  programmatic_foundation_project
                ADD
                  CONSTRAINT FK_8E8E96D55DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  programmatic_foundation_project_tag
                ADD
                  CONSTRAINT FK_9F63872166D1F9C FOREIGN KEY (project_id) REFERENCES programmatic_foundation_project (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  programmatic_foundation_project_tag
                ADD
                  CONSTRAINT FK_9F63872BAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  programmatic_foundation_sub_approach
                ADD
                  CONSTRAINT FK_735C1D0115140614 FOREIGN KEY (approach_id) REFERENCES programmatic_foundation_approach (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql('ALTER TABLE adherent_message_filters ADD user_list_definition_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                ADD
                  CONSTRAINT FK_28CA9F94F74563E3 FOREIGN KEY (user_list_definition_id) REFERENCES user_list_definition (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql('CREATE INDEX IDX_28CA9F94F74563E3 ON adherent_message_filters (user_list_definition_id)');
        $this->addSql('ALTER TABLE adherents ADD election_results_reporter TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
