<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250227133157 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          referrer_id INT UNSIGNED DEFAULT NULL,
        ADD
          referrer_code VARCHAR(7) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          national_event_inscription
        ADD
          CONSTRAINT FK_C3325557798C22DB FOREIGN KEY (referrer_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE INDEX IDX_C3325557798C22DB ON national_event_inscription (referrer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event_inscription DROP FOREIGN KEY FK_C3325557798C22DB');
        $this->addSql('DROP INDEX IDX_C3325557798C22DB ON national_event_inscription');
        $this->addSql('ALTER TABLE national_event_inscription DROP referrer_id, DROP referrer_code');
    }
}
