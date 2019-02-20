<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180611152723 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_attachment_file ADD slug VARCHAR(255) NOT NULL, DROP uuid');
        $this->addSql('CREATE UNIQUE INDEX mooc_attachment_file_slug ON mooc_attachment_file (slug, extension)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX mooc_attachment_file_slug ON mooc_attachment_file');
        $this->addSql('ALTER TABLE mooc_attachment_file ADD uuid CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', DROP slug');
    }
}
