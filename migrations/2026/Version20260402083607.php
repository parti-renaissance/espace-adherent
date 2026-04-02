<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260402083607 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailchimp_campaign ADD retry_count SMALLINT UNSIGNED DEFAULT 0 NOT NULL, ADD retry_history JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailchimp_campaign DROP retry_count, DROP retry_history');
    }
}
