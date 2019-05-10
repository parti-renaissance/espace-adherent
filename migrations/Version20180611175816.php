<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180611175816 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_attachment_file RENAME INDEX mooc_attachment_file_slug TO mooc_attachment_file_slug_extension');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_attachment_file RENAME INDEX mooc_attachment_file_slug_extension TO mooc_attachment_file_slug');
    }
}
