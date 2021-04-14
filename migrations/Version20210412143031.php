<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210412143031 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cause ADD followers_count INT UNSIGNED NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('
            UPDATE cause 
            SET followers_count = (SELECT COUNT(1) FROM cause_follower WHERE cause_follower.cause_id = cause.id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cause DROP followers_count');
    }
}
