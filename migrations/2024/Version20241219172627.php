<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241219172627 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jemengage_deep_link DROP FOREIGN KEY FK_AB0E52829DF5350C');
        $this->addSql('ALTER TABLE jemengage_deep_link DROP FOREIGN KEY FK_AB0E5282CF1918FF');
        $this->addSql('DROP TABLE jemengage_deep_link');
        $this->addSql('ALTER TABLE events DROP dynamic_link');
        $this->addSql('ALTER TABLE jecoute_news DROP dynamic_link');
        $this->addSql('ALTER TABLE jecoute_riposte DROP dynamic_link');
        $this->addSql('ALTER TABLE jecoute_survey DROP dynamic_link');
        $this->addSql('ALTER TABLE pap_campaign DROP dynamic_link');
        $this->addSql('ALTER TABLE phoning_campaign DROP dynamic_link');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jemengage_deep_link (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          label VARCHAR(255) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
            link VARCHAR(255) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
            social_title VARCHAR(255) CHARACTER
          SET
            utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            social_description VARCHAR(255) CHARACTER
          SET
            utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            uuid CHAR(36) CHARACTER
          SET
            utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            dynamic_link VARCHAR(255) CHARACTER
          SET
            utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            UNIQUE INDEX UNIQ_AB0E5282D17F50A6 (uuid),
            INDEX IDX_AB0E52829DF5350C (created_by_administrator_id),
            INDEX IDX_AB0E5282CF1918FF (updated_by_administrator_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          jemengage_deep_link
        ADD
          CONSTRAINT FK_AB0E52829DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON
        UPDATE
          NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jemengage_deep_link
        ADD
          CONSTRAINT FK_AB0E5282CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON
        UPDATE
          NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE `events` ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_news ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_riposte ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_survey ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pap_campaign ADD dynamic_link VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE phoning_campaign ADD dynamic_link VARCHAR(255) DEFAULT NULL');
    }
}
