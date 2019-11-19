<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191119002650 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          mooc_elements 
        ADD 
          image_id INT UNSIGNED DEFAULT NULL, 
          CHANGE content content LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          mooc_elements 
        ADD 
          CONSTRAINT FK_691284C53DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('CREATE INDEX IDX_691284C53DA5256D ON mooc_elements (image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements DROP FOREIGN KEY FK_691284C53DA5256D');
        $this->addSql('DROP INDEX IDX_691284C53DA5256D ON mooc_elements');
        $this->addSql('ALTER TABLE 
          mooc_elements 
        DROP 
          image_id, 
          CHANGE content content VARCHAR(800) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
