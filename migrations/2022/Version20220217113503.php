<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220217113503 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          jecoute_news
        ADD
          link_label VARCHAR(255) DEFAULT NULL,
        ADD
          pinned TINYINT(1) DEFAULT \'0\' NOT NULL,
        ADD
          enriched TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_news DROP link_label, DROP pinned, DROP enriched');
    }
}
