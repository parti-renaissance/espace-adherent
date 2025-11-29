<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220414211940 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX projection_managed_users_search ON projection_managed_users');
        $this->addSql('CREATE INDEX IDX_90A7D6567B00651C ON projection_managed_users (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_90A7D6567B00651C ON projection_managed_users');
        $this->addSql('CREATE INDEX projection_managed_users_search ON projection_managed_users (status, postal_code, country)');
    }
}
