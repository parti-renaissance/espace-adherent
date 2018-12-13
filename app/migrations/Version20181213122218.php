<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181213122218 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE citizen_project_referent_tag (citizen_project_id INT UNSIGNED NOT NULL, referent_tag_id INT UNSIGNED NOT NULL, INDEX IDX_73ED204AB3584533 (citizen_project_id), INDEX IDX_73ED204A9C262DB3 (referent_tag_id), PRIMARY KEY(citizen_project_id, referent_tag_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE citizen_project_referent_tag ADD CONSTRAINT FK_73ED204AB3584533 FOREIGN KEY (citizen_project_id) REFERENCES citizen_projects (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_project_referent_tag ADD CONSTRAINT FK_73ED204A9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE citizen_project_referent_tag');
    }
}
