<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171114160211 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE adherent_adherent_tag (adherent_id INT UNSIGNED NOT NULL, adherent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_DD297F8225F06C53 (adherent_id), INDEX IDX_DD297F82AED03543 (adherent_tag_id), PRIMARY KEY(adherent_id, adherent_tag_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherent_adherent_tag ADD CONSTRAINT FK_DD297F8225F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adherent_adherent_tag ADD CONSTRAINT FK_DD297F82AED03543 FOREIGN KEY (adherent_tag_id) REFERENCES adherent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE adherent_adherent_tag');
    }
}
