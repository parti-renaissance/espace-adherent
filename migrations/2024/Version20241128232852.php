<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241128232852 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD resubscribe_email_sent_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_managed_users ADD resubscribe_email_sent_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP subscribe_email_sent_at');
        $this->addSql('ALTER TABLE projection_managed_users DROP subscribe_email_sent_at');
    }
}
