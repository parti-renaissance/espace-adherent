<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191107091244 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE programmatic_foundation_sub_approach (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          approach_id INT UNSIGNED DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          subtitle VARCHAR(255) DEFAULT NULL, 
          content VARCHAR(255) DEFAULT NULL, 
          is_expanded TINYINT(1) NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_735C1D0115140614 (approach_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programmatic_foundation_project (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          measure_id INT UNSIGNED DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          content LONGTEXT NOT NULL, 
          city VARCHAR(255) NOT NULL, 
          is_expanded TINYINT(1) NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_8E8E96D55DA37D00 (measure_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programmatic_foundation_project_tag (
          project_id INT UNSIGNED NOT NULL, 
          tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_9F63872166D1F9C (project_id), 
          INDEX IDX_9F63872BAD26311 (tag_id), 
          PRIMARY KEY(project_id, tag_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programmatic_foundation_measure (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          approach_id INT UNSIGNED DEFAULT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          slug VARCHAR(255) NOT NULL, 
          content LONGTEXT NOT NULL, 
          is_leading TINYINT(1) NOT NULL, 
          city VARCHAR(255) NOT NULL, 
          is_expanded TINYINT(1) NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          UNIQUE INDEX UNIQ_213A5F1E989D9B62 (slug), 
          INDEX IDX_213A5F1E15140614 (approach_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programmatic_foundation_measure_tag (
          measure_id INT UNSIGNED NOT NULL, 
          tag_id INT UNSIGNED NOT NULL, 
          INDEX IDX_F004297F5DA37D00 (measure_id), 
          INDEX IDX_F004297FBAD26311 (tag_id), 
          PRIMARY KEY(measure_id, tag_id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programmatic_foundation_approach (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          position SMALLINT NOT NULL, 
          title VARCHAR(255) NOT NULL, 
          content VARCHAR(255) DEFAULT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programmatic_foundation_tag (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          label VARCHAR(100) NOT NULL, 
          UNIQUE INDEX UNIQ_12127927EA750E8 (label), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_sub_approach 
        ADD 
          CONSTRAINT FK_735C1D0115140614 FOREIGN KEY (approach_id) REFERENCES programmatic_foundation_approach (id)');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_project 
        ADD 
          CONSTRAINT FK_8E8E96D55DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id)');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_project_tag 
        ADD 
          CONSTRAINT FK_9F63872166D1F9C FOREIGN KEY (project_id) REFERENCES programmatic_foundation_project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_project_tag 
        ADD 
          CONSTRAINT FK_9F63872BAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure 
        ADD 
          CONSTRAINT FK_213A5F1E15140614 FOREIGN KEY (approach_id) REFERENCES programmatic_foundation_sub_approach (id)');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure_tag 
        ADD 
          CONSTRAINT FK_F004297F5DA37D00 FOREIGN KEY (measure_id) REFERENCES programmatic_foundation_measure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          programmatic_foundation_measure_tag 
        ADD 
          CONSTRAINT FK_F004297FBAD26311 FOREIGN KEY (tag_id) REFERENCES programmatic_foundation_tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE programmatic_foundation_measure DROP FOREIGN KEY FK_213A5F1E15140614');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP FOREIGN KEY FK_9F63872166D1F9C');
        $this->addSql('ALTER TABLE programmatic_foundation_project DROP FOREIGN KEY FK_8E8E96D55DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP FOREIGN KEY FK_F004297F5DA37D00');
        $this->addSql('ALTER TABLE programmatic_foundation_sub_approach DROP FOREIGN KEY FK_735C1D0115140614');
        $this->addSql('ALTER TABLE programmatic_foundation_project_tag DROP FOREIGN KEY FK_9F63872BAD26311');
        $this->addSql('ALTER TABLE programmatic_foundation_measure_tag DROP FOREIGN KEY FK_F004297FBAD26311');
        $this->addSql('DROP TABLE programmatic_foundation_sub_approach');
        $this->addSql('DROP TABLE programmatic_foundation_project');
        $this->addSql('DROP TABLE programmatic_foundation_project_tag');
        $this->addSql('DROP TABLE programmatic_foundation_measure');
        $this->addSql('DROP TABLE programmatic_foundation_measure_tag');
        $this->addSql('DROP TABLE programmatic_foundation_approach');
        $this->addSql('DROP TABLE programmatic_foundation_tag');
    }
}
