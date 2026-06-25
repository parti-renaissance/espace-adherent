<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612114124 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_2248B1DEA166B6B7 ON timeline_feed (publication_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_2248B1DEA166B6B7 ON timeline_feed');
    }
}
