<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230927161359 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE designation_candidacy_pool (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          label VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_4DE072DAD17F50A6 (uuid),
          INDEX IDX_4DE072DAFAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE designation_candidacy_pool_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          candidacy_pool_id INT UNSIGNED NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_A9E819837B63808A (candidacy_pool_id),
          INDEX IDX_A9E819839DF5350C (created_by_administrator_id),
          INDEX IDX_A9E81983CF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE designation_candidacy_pool_candidacy (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          candidacy_pool_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          gender VARCHAR(255) NOT NULL,
          biography LONGTEXT DEFAULT NULL,
          first_name VARCHAR(255) NOT NULL,
          last_name VARCHAR(255) NOT NULL,
          is_substitute TINYINT(1) DEFAULT 0 NOT NULL,
          status VARCHAR(255) NOT NULL,
          faith_statement LONGTEXT DEFAULT NULL,
          is_public_faith_statement TINYINT(1) DEFAULT 0 NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          position INT NOT NULL,
          UNIQUE INDEX UNIQ_A4C6328BD17F50A6 (uuid),
          INDEX IDX_A4C6328B7B63808A (candidacy_pool_id),
          INDEX IDX_A4C6328BFC1537C1 (candidacies_group_id),
          INDEX IDX_A4C6328B25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          designation_candidacy_pool
        ADD
          CONSTRAINT FK_4DE072DAFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          designation_candidacy_pool_candidacies_group
        ADD
          CONSTRAINT FK_A9E819837B63808A FOREIGN KEY (candidacy_pool_id) REFERENCES designation_candidacy_pool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_candidacy_pool_candidacies_group
        ADD
          CONSTRAINT FK_A9E819839DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          designation_candidacy_pool_candidacies_group
        ADD
          CONSTRAINT FK_A9E81983CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          designation_candidacy_pool_candidacy
        ADD
          CONSTRAINT FK_A4C6328B7B63808A FOREIGN KEY (candidacy_pool_id) REFERENCES designation_candidacy_pool (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_candidacy_pool_candidacy
        ADD
          CONSTRAINT FK_A4C6328BFC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES designation_candidacy_pool_candidacies_group (id)');
        $this->addSql('ALTER TABLE
          designation_candidacy_pool_candidacy
        ADD
          CONSTRAINT FK_A4C6328B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation_candidacy_pool DROP FOREIGN KEY FK_4DE072DAFAC7D83F');
        $this->addSql('ALTER TABLE designation_candidacy_pool_candidacies_group DROP FOREIGN KEY FK_A9E819837B63808A');
        $this->addSql('ALTER TABLE designation_candidacy_pool_candidacies_group DROP FOREIGN KEY FK_A9E819839DF5350C');
        $this->addSql('ALTER TABLE designation_candidacy_pool_candidacies_group DROP FOREIGN KEY FK_A9E81983CF1918FF');
        $this->addSql('ALTER TABLE designation_candidacy_pool_candidacy DROP FOREIGN KEY FK_A4C6328B7B63808A');
        $this->addSql('ALTER TABLE designation_candidacy_pool_candidacy DROP FOREIGN KEY FK_A4C6328BFC1537C1');
        $this->addSql('ALTER TABLE designation_candidacy_pool_candidacy DROP FOREIGN KEY FK_A4C6328B25F06C53');
        $this->addSql('DROP TABLE designation_candidacy_pool');
        $this->addSql('DROP TABLE designation_candidacy_pool_candidacies_group');
        $this->addSql('DROP TABLE designation_candidacy_pool_candidacy');
    }
}
