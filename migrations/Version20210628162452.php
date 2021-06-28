<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210628162452 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE national_council_candidacies_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE national_council_candidacy (
          id INT AUTO_INCREMENT NOT NULL,
          election_id INT UNSIGNED NOT NULL,
          candidacies_group_id INT UNSIGNED DEFAULT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          gender VARCHAR(255) NOT NULL,
          biography LONGTEXT DEFAULT NULL,
          quality VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          status VARCHAR(255) NOT NULL,
          faith_statement LONGTEXT DEFAULT NULL,
          is_public_faith_statement TINYINT(1) DEFAULT \'0\' NOT NULL,
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          image_name VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_31A7A205D17F50A6 (uuid),
          INDEX IDX_31A7A205A708DAFF (election_id),
          INDEX IDX_31A7A205FC1537C1 (candidacies_group_id),
          INDEX IDX_31A7A20525F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE national_council_election (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          designation_id INT UNSIGNED DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_F3809347FAC7D83F (designation_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A205A708DAFF FOREIGN KEY (election_id) REFERENCES national_council_election (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A205FC1537C1 FOREIGN KEY (candidacies_group_id) REFERENCES national_council_candidacies_group (id)');
        $this->addSql('ALTER TABLE
          national_council_candidacy
        ADD
          CONSTRAINT FK_31A7A20525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          national_council_election
        ADD
          CONSTRAINT FK_F3809347FAC7D83F FOREIGN KEY (designation_id) REFERENCES designation (id)');
        $this->addSql('ALTER TABLE
          designation
        ADD
          result_schedule_delay DOUBLE PRECISION UNSIGNED DEFAULT \'0\' NOT NULL,
        ADD
          notifications INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A205FC1537C1');
        $this->addSql('ALTER TABLE national_council_candidacy DROP FOREIGN KEY FK_31A7A205A708DAFF');
        $this->addSql('DROP TABLE national_council_candidacies_group');
        $this->addSql('DROP TABLE national_council_candidacy');
        $this->addSql('DROP TABLE national_council_election');
        $this->addSql('ALTER TABLE designation DROP result_schedule_delay, DROP notifications');
    }
}
