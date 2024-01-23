<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240123220055 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          renaissance_newsletter_subscription
        ADD
          source VARCHAR(255) DEFAULT NULL,
        CHANGE
          first_name first_name VARCHAR(255) DEFAULT NULL,
        CHANGE
          zip_code zip_code VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          renaissance_newsletter_subscription
        DROP
          source,
        CHANGE
          first_name first_name VARCHAR(255) NOT NULL,
        CHANGE
          zip_code zip_code VARCHAR(255) NOT NULL');
    }
}
