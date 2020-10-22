<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20201022163132 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE managed_area (id INT UNSIGNED AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE managed_area_referent_tag (managed_area_id INT UNSIGNED NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_3F9497EBDC184E71 (managed_area_id), INDEX IDX_3F9497EB9C262DB3 (referent_tag_id), PRIMARY KEY(managed_area_id, referent_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE managed_area_referent_tag ADD CONSTRAINT FK_3F9497EBDC184E71 FOREIGN KEY (managed_area_id) REFERENCES managed_area (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE managed_area_referent_tag ADD CONSTRAINT FK_3F9497EB9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('ALTER TABLE adherents ADD regional_candidate_managed_area_id INT UNSIGNED DEFAULT NULL, ADD departmental_candidate_managed_area_id INT UNSIGNED DEFAULT NULL, ADD tdl_departmental_candidate_managed_area_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA352CF6C72 FOREIGN KEY (regional_candidate_managed_area_id) REFERENCES managed_area (id)');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA37774366C FOREIGN KEY (departmental_candidate_managed_area_id) REFERENCES managed_area (id)');
        $this->addSql('ALTER TABLE adherents ADD CONSTRAINT FK_562C7DA3A3B5EA7E FOREIGN KEY (tdl_departmental_candidate_managed_area_id) REFERENCES managed_area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA352CF6C72 ON adherents (regional_candidate_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA37774366C ON adherents (departmental_candidate_managed_area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3A3B5EA7E ON adherents (tdl_departmental_candidate_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA352CF6C72');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA37774366C');
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3A3B5EA7E');
        $this->addSql('ALTER TABLE managed_area_referent_tag DROP FOREIGN KEY FK_3F9497EBDC184E71');
        $this->addSql('DROP TABLE managed_area');
        $this->addSql('DROP TABLE managed_area_referent_tag');
        $this->addSql('DROP INDEX UNIQ_562C7DA352CF6C72 ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA37774366C ON adherents');
        $this->addSql('DROP INDEX UNIQ_562C7DA3A3B5EA7E ON adherents');
        $this->addSql('ALTER TABLE adherents DROP regional_candidate_managed_area_id, DROP departmental_candidate_managed_area_id, DROP tdl_departmental_candidate_managed_area_id');
    }
}
