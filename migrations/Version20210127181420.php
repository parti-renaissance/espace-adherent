<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210127181420 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          events 
        ADD 
          visio_url VARCHAR(255) DEFAULT NULL, 
        ADD 
          interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
        ADD 
          image_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events DROP visio_url, DROP interests, DROP image_name');
    }
}
