<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190108152424 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX idea_workshop_thread_status_idx ON ideas_workshop_thread');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD approved TINYINT(1) NOT NULL, DROP status');
        $this->addSql('DROP INDEX idea_workshop_thread_comments_status_idx ON ideas_workshop_comment');
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD approved TINYINT(1) NOT NULL, DROP status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD status VARCHAR(9) DEFAULT \'POSTED\' NOT NULL COLLATE utf8_unicode_ci, DROP approved');
        $this->addSql('CREATE INDEX idea_workshop_thread_comments_status_idx ON ideas_workshop_comment (status)');
        $this->addSql('ALTER TABLE ideas_workshop_thread ADD status VARCHAR(9) DEFAULT \'POSTED\' NOT NULL COLLATE utf8_unicode_ci, DROP approved');
        $this->addSql('CREATE INDEX idea_workshop_thread_status_idx ON ideas_workshop_thread (status)');
    }
}
