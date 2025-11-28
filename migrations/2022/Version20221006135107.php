<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221006135107 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audience ADD is_renaissance_membership TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE audience_snapshot ADD is_renaissance_membership TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audience DROP is_renaissance_membership');
        $this->addSql('ALTER TABLE audience_snapshot DROP is_renaissance_membership');
    }
}
