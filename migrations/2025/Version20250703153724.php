<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250703153724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_messages ADD author_theme JSON DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE events ADD author_theme JSON DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE jecoute_news ADD author_theme JSON DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE vox_action ADD author_theme JSON DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_messages DROP author_theme
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE `events` DROP author_theme
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE jecoute_news DROP author_theme
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE vox_action DROP author_theme
            SQL);
    }
}
