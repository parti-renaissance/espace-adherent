<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180614171252 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc ADD content VARCHAR(800) DEFAULT NULL, ADD youtube_id VARCHAR(255) NOT NULL, ADD youtube_duration TIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc DROP content, DROP youtube_id, DROP youtube_duration');
    }
}
