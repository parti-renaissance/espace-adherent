<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250710200648 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_message_filters DROP renaissance_membership
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE audience DROP renaissance_membership
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE audience_snapshot DROP renaissance_membership
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_message_filters ADD renaissance_membership VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE audience ADD renaissance_membership VARCHAR(255) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE audience_snapshot ADD renaissance_membership VARCHAR(255) DEFAULT NULL
            SQL);
    }
}
