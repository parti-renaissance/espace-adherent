<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250131092016 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE general_convention (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          department_zone_id INT UNSIGNED DEFAULT NULL,
          committee_zone_id INT UNSIGNED DEFAULT NULL,
          district_zone_id INT UNSIGNED DEFAULT NULL,
          reporter_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          organizer VARCHAR(255) NOT NULL,
          reported_at DATETIME NOT NULL,
          meeting_type VARCHAR(255) NOT NULL,
          members_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          participant_quality VARCHAR(255) NOT NULL,
          general_summary LONGTEXT DEFAULT NULL,
          party_definition_summary LONGTEXT DEFAULT NULL,
          unique_party_summary LONGTEXT DEFAULT NULL,
          progress_since2016 LONGTEXT DEFAULT NULL,
          party_objectives LONGTEXT DEFAULT NULL,
          governance LONGTEXT DEFAULT NULL,
          communication LONGTEXT DEFAULT NULL,
          militant_training LONGTEXT DEFAULT NULL,
          member_journey LONGTEXT DEFAULT NULL,
          mobilization LONGTEXT DEFAULT NULL,
          talent_detection LONGTEXT DEFAULT NULL,
          election_preparation LONGTEXT DEFAULT NULL,
          relationship_with_supporters LONGTEXT DEFAULT NULL,
          work_with_partners LONGTEXT DEFAULT NULL,
          additional_comments LONGTEXT DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_F66947EFD17F50A6 (uuid),
          INDEX IDX_F66947EF2285D748 (department_zone_id),
          INDEX IDX_F66947EF94819E3B (committee_zone_id),
          INDEX IDX_F66947EF23F5C396 (district_zone_id),
          INDEX IDX_F66947EFE1CFE6F5 (reporter_id),
          INDEX IDX_F66947EF9DF5350C (created_by_administrator_id),
          INDEX IDX_F66947EFCF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EF2285D748 FOREIGN KEY (department_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EF94819E3B FOREIGN KEY (committee_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EF23F5C396 FOREIGN KEY (district_zone_id) REFERENCES geo_zone (id)');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EFE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EF9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          general_convention
        ADD
          CONSTRAINT FK_F66947EFCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EF2285D748');
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EF94819E3B');
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EF23F5C396');
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EFE1CFE6F5');
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EF9DF5350C');
        $this->addSql('ALTER TABLE general_convention DROP FOREIGN KEY FK_F66947EFCF1918FF');
        $this->addSql('DROP TABLE general_convention');
    }
}
