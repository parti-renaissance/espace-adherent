<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180618103523 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements ADD email_body VARCHAR(800) NOT NULL, CHANGE email_text email_object VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements DROP email_body, CHANGE email_object email_text VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
