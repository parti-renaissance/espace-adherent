<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181228151600 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idea_workshop_thread_status_idx ON ideas_workshop_thread (status)');
        $this->addSql('CREATE UNIQUE INDEX threads_uuid_unique ON ideas_workshop_thread (uuid)');
        $this->addSql('CREATE INDEX idea_workshop_thread_comments_status_idx ON ideas_workshop_comment (status)');
        $this->addSql('CREATE UNIQUE INDEX threads_comments_uuid_unique ON ideas_workshop_comment (uuid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idea_workshop_thread_comments_status_idx ON ideas_workshop_comment');
        $this->addSql('DROP INDEX threads_comments_uuid_unique ON ideas_workshop_comment');
        $this->addSql('DROP INDEX idea_workshop_thread_status_idx ON ideas_workshop_thread');
        $this->addSql('DROP INDEX threads_uuid_unique ON ideas_workshop_thread');
    }
}
