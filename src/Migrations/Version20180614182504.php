<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180614182504 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements ADD twitter_text VARCHAR(255) NOT NULL, ADD facebook_text VARCHAR(255) NOT NULL, ADD email_text VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements DROP twitter_text, DROP facebook_text, DROP email_text');
    }
}
