<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200429105408 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE newsletter_subscriptions ADD confirmed_at DATETIME DEFAULT NULL, ADD uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', ADD token CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3C13B0BD17F50A6 ON newsletter_subscriptions (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B3C13B0B5F37A13B ON newsletter_subscriptions (token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_B3C13B0BD17F50A6 ON newsletter_subscriptions');
        $this->addSql('DROP INDEX UNIQ_B3C13B0B5F37A13B ON newsletter_subscriptions');
        $this->addSql('ALTER TABLE newsletter_subscriptions DROP confirmed_at, DROP uuid, DROP token');
    }
}
