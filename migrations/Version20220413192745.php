<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220413192745 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          legislative_newsletter_subscription
        ADD
          token CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\',
        ADD
          confirmed_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2672FB765F37A13B ON legislative_newsletter_subscription (token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_2672FB765F37A13B ON legislative_newsletter_subscription');
        $this->addSql('ALTER TABLE legislative_newsletter_subscription DROP token, DROP confirmed_at');
    }
}
