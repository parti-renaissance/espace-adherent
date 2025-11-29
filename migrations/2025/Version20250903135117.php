<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250903135117 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        ADD
          resubscribe_email_started_at DATETIME DEFAULT NULL,
        ADD
          resubscribe_response LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP resubscribe_email_started_at, DROP resubscribe_response');
    }
}
