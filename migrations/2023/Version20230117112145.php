<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230117112145 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $checkKey = $this->connection
            ->executeQuery("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE
                    CONSTRAINT_SCHEMA = DATABASE() AND
                    TABLE_NAME        = 'adherents' AND
                    CONSTRAINT_NAME   = 'FK_562C7DA3CC72679B' AND
                    CONSTRAINT_TYPE   = 'FOREIGN KEY'")
            ->fetchFirstColumn()
        ;

        if ($checkKey) {
            $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3CC72679B');
        }

        $checkKey = $this->connection
            ->executeQuery("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE
                    CONSTRAINT_SCHEMA = DATABASE() AND
                    TABLE_NAME        = 'adherents' AND
                    CONSTRAINT_NAME   = 'FK_562C7DA39E544A1' AND
                    CONSTRAINT_TYPE   = 'FOREIGN KEY'")
            ->fetchFirstColumn()
        ;

        if ($checkKey) {
            $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39E544A1');
        }

        $this->addSql('DROP TABLE municipal_chief_areas');
        $this->addSql('ALTER TABLE
              adherent_message_filters
            DROP
              contact_volunteer_team,
            DROP
              contact_running_mate_team,
            DROP
              contact_adherents,
            DROP
              insee_code,
            DROP
              contact_newsletter');
        $this->addSql('DROP INDEX UNIQ_562C7DA3CC72679B ON adherents');
        $this->addSql('ALTER TABLE adherents DROP municipal_chief_managed_area_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE municipal_chief_areas (
              id INT AUTO_INCREMENT NOT NULL,
              jecoute_access TINYINT(1) DEFAULT \'0\' NOT NULL,
              insee_code VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
              adherent_message_filters
            ADD
              contact_volunteer_team TINYINT(1) DEFAULT \'0\',
            ADD
              contact_running_mate_team TINYINT(1) DEFAULT \'0\',
            ADD
              contact_adherents TINYINT(1) DEFAULT \'0\',
            ADD
              insee_code VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
            ADD
              contact_newsletter TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE adherents ADD municipal_chief_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
              adherents
            ADD
              CONSTRAINT FK_562C7DA3CC72679B FOREIGN KEY (
                municipal_chief_managed_area_id
              ) REFERENCES municipal_chief_areas (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3CC72679B ON adherents (municipal_chief_managed_area_id)');
    }
}
