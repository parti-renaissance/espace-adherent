<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310140609 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherents
                ADD
                  instagram_page_url VARCHAR(255) DEFAULT NULL,
                ADD
                  tiktok_page_url VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql('ALTER TABLE projection_managed_users ADD tiktok_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP instagram_page_url, DROP tiktok_page_url');
        $this->addSql('ALTER TABLE projection_managed_users DROP tiktok_url');
    }
}
