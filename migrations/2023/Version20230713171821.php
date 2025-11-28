<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230713171821 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA379645AD5');
        $this->addSql('ALTER TABLE lre_area DROP FOREIGN KEY FK_8D3B8F189C262DB3');
        $this->addSql('DROP TABLE lre_area');
        $this->addSql('DROP INDEX UNIQ_562C7DA379645AD5 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP lre_area_id');

        $this->addSql('DELETE FROM adherent_messages WHERE type = \'lre_manager_elected_representative\'');
        $this->addSql('DELETE FROM adherent_message_filters WHERE dtype = \'lremanagerelectedrepresentativefilter\'');
        $this->addSql('DELETE FROM adherent_charter WHERE dtype = \'lrecharter\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lre_area (
          id INT AUTO_INCREMENT NOT NULL,
          referent_tag_id INT UNSIGNED DEFAULT NULL,
          all_tags TINYINT(1) DEFAULT 0 NOT NULL,
          INDEX IDX_8D3B8F189C262DB3 (referent_tag_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          lre_area
        ADD
          CONSTRAINT FK_8D3B8F189C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE adherents ADD lre_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA379645AD5 FOREIGN KEY (lre_area_id) REFERENCES lre_area (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA379645AD5 ON adherents (lre_area_id)');
    }
}
