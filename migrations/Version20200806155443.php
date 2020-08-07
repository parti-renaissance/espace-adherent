<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200806155443 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lre_area ADD all_tags TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE lre_area CHANGE referent_tag_id referent_tag_id INT UNSIGNED DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lre_area DROP all_tags');
        $this->addSql('ALTER TABLE lre_area CHANGE referent_tag_id referent_tag_id INT UNSIGNED NOT NULL');
    }
}
