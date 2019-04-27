<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180605143145 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX mooc_chapter_order_display_by_mooc ON mooc_chapter');
        $this->addSql('ALTER TABLE mooc_chapter CHANGE display_order display_order SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE mooc_elements CHANGE display_order display_order SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_chapter CHANGE display_order display_order SMALLINT DEFAULT 1 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX mooc_chapter_order_display_by_mooc ON mooc_chapter (display_order, mooc_id)');
        $this->addSql('ALTER TABLE mooc_elements CHANGE display_order display_order SMALLINT DEFAULT 1 NOT NULL');
    }
}
