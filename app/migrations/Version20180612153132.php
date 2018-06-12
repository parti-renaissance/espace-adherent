<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180612153132 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements ADD typeform_url VARCHAR(255) DEFAULT NULL, DROP type_form');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements ADD type_form LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, DROP typeform_url');
    }
}
