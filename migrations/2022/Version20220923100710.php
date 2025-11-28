<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220923100710 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE renaissance_newsletter_subscription DROP country');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          renaissance_newsletter_subscription
        ADD
          country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
