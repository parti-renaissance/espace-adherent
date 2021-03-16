<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200819100824 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD membership_reminded_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE adherents SET membership_reminded_at = NOW() WHERE activated_at < DATE_SUB(NOW(), INTERVAL 2 DAY)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP membership_reminded_at');
    }
}
