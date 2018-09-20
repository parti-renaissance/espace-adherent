<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180924100424 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE districts ADD referent_tag_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE districts ADD CONSTRAINT FK_68E318DC9C262DB3 FOREIGN KEY (referent_tag_id) REFERENCES referent_tags (id)');
        $this->addSql('CREATE UNIQUE INDEX district_referent_tag_unique ON districts (referent_tag_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE districts DROP FOREIGN KEY FK_68E318DC9C262DB3');
        $this->addSql('DROP INDEX district_referent_tag_unique ON districts');
        $this->addSql('ALTER TABLE districts DROP referent_tag_id');
    }
}
