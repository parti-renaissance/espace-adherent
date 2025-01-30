<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241119155704 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          events
        ADD
          image_size BIGINT DEFAULT NULL,
        ADD
          image_mime_type VARCHAR(50) DEFAULT NULL,
        ADD
          image_width INT DEFAULT NULL,
        ADD
          image_height INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          `events`
        DROP
          image_size,
        DROP
          image_mime_type,
        DROP
          image_width,
        DROP
          image_height');
    }
}
