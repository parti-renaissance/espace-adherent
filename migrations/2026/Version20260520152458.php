<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520152458 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_90A7D6567B00651C ON projection_managed_users');
        $this->addSql('ALTER TABLE projection_managed_users DROP status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_managed_users ADD status SMALLINT NOT NULL');
        $this->addSql('CREATE INDEX IDX_90A7D6567B00651C ON projection_managed_users (status)');
    }
}
