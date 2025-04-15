<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250415094308 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE push_token ADD app_session_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          CONSTRAINT FK_51BC1381372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_51BC1381372447A3 ON push_token (app_session_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE push_token DROP FOREIGN KEY FK_51BC1381372447A3');
        $this->addSql('DROP INDEX IDX_51BC1381372447A3 ON push_token');
        $this->addSql('ALTER TABLE push_token DROP app_session_id');
    }
}
