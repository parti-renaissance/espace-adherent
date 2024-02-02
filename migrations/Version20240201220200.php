<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240201220200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_562C7DA36FBC9426 ON adherents (tags)');
        $this->addSql('CREATE INDEX IDX_90A7D6566FBC9426 ON projection_managed_users (tags)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_562C7DA36FBC9426 ON adherents');
        $this->addSql('DROP INDEX IDX_90A7D6566FBC9426 ON projection_managed_users');
    }
}
