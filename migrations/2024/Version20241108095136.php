<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241108095136 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation ADD alert_begin_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE designation SET alert_begin_at = DATE_SUB(vote_start_date, INTERVAL 2 DAY) WHERE vote_start_date IS NOT NULL AND type IN (\'consultation\', \'vote\')');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP alert_begin_at');
    }
}
