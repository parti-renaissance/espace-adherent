<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260414144956 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  oauth_clients
                ADD
                  pkce_required TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  post_logout_redirect_uris JSON DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_clients DROP pkce_required, DROP post_logout_redirect_uris');
    }
}
