<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221122094902 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE designation_zone (
          designation_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_19505C8CFAC7D83F (designation_id),
          INDEX IDX_19505C8C9F2C3FAB (zone_id),
          PRIMARY KEY(designation_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE local_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          UNIQUE INDEX UNIQ_4F341298D17F50A6 (uuid),
          INDEX IDX_4F341298FAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE local_election_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          faith_statement_file_name VARCHAR(255) DEFAULT NULL,
          INDEX IDX_8D478DE8A708DAFF (election_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE local_election_candidacy (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          gender VARCHAR(255) NOT NULL,
          biography LONGTEXT DEFAULT NULL,
          first_name VARCHAR(255) NOT NULL,
          last_name VARCHAR(255) NOT NULL,
          email VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(255) NOT NULL,
          faith_statement LONGTEXT DEFAULT NULL,
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          position INT NOT NULL,
          UNIQUE INDEX UNIQ_77220D7DD17F50A6 (uuid),
          INDEX IDX_77220D7DA708DAFF (election_id),
          INDEX IDX_77220D7DFC1537C1 (candidacies_group_id),
          INDEX IDX_77220D7D25F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          designation_zone
        ADD
          CONSTRAINT FK_19505C8CFAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          designation_zone
        ADD
          CONSTRAINT FK_19505C8C9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          local_election
        ADD
          CONSTRAINT FK_4F341298FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          local_election_candidacies_group
        ADD
          CONSTRAINT FK_8D478DE8A708DAFF FOREIGN KEY (election_id) REFERENCES local_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          local_election_candidacy
        ADD
          CONSTRAINT FK_77220D7DA708DAFF FOREIGN KEY (election_id) REFERENCES local_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          local_election_candidacy
        ADD
          CONSTRAINT FK_77220D7DFC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES local_election_candidacies_group (id)');
        $this->addSql('ALTER TABLE
          local_election_candidacy
        ADD
          CONSTRAINT FK_77220D7D25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          designation
        CHANGE
          candidacy_start_date candidacy_start_date DATETIME DEFAULT NULL,
        CHANGE
          zones global_zones LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE local_election_candidacies_group DROP FOREIGN KEY FK_8D478DE8A708DAFF');
        $this->addSql('ALTER TABLE local_election_candidacy DROP FOREIGN KEY FK_77220D7DA708DAFF');
        $this->addSql('ALTER TABLE local_election_candidacy DROP FOREIGN KEY FK_77220D7DFC1537C1');
        $this->addSql('DROP TABLE designation_zone');
        $this->addSql('DROP TABLE local_election');
        $this->addSql('DROP TABLE local_election_candidacies_group');
        $this->addSql('DROP TABLE local_election_candidacy');
        $this->addSql('ALTER TABLE
          designation
        CHANGE
          candidacy_start_date candidacy_start_date DATETIME NOT NULL,
        CHANGE
          global_zones zones LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:simple_array)\'');
    }
}
