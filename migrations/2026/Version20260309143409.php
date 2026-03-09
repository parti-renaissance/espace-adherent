<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309143409 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                ADD
                  last_activity_at DATETIME DEFAULT NULL,
                ADD
                  twitter_url VARCHAR(255) DEFAULT NULL,
                ADD
                  facebook_url VARCHAR(255) DEFAULT NULL,
                ADD
                  instagram_url VARCHAR(255) DEFAULT NULL,
                ADD
                  linkedin_url VARCHAR(255) DEFAULT NULL,
                ADD
                  telegram_url VARCHAR(255) DEFAULT NULL,
                ADD
                  sessions JSON DEFAULT NULL,
                ADD
                  adherent_tags JSON DEFAULT NULL,
                ADD
                  static_tags JSON DEFAULT NULL,
                ADD
                  elect_tags JSON DEFAULT NULL,
                ADD
                  instances JSON DEFAULT NULL,
                ADD
                  subscriptions JSON DEFAULT NULL,
                ADD
                  civility VARCHAR(20) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                DROP
                  last_activity_at,
                DROP
                  twitter_url,
                DROP
                  facebook_url,
                DROP
                  instagram_url,
                DROP
                  linkedin_url,
                DROP
                  telegram_url,
                DROP
                  sessions,
                DROP
                  adherent_tags,
                DROP
                  static_tags,
                DROP
                  elect_tags,
                DROP
                  instances,
                DROP
                  subscriptions,
                DROP
                  civility
            SQL);
    }
}
