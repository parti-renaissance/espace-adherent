<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241002124458 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE events ADD author_scope VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE jecoute_news ADD author_scope VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vox_action ADD author_scope VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `events` DROP author_scope');
        $this->addSql('ALTER TABLE jecoute_news DROP author_scope');
        $this->addSql('ALTER TABLE vox_action DROP author_scope');
    }
}
