<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180620111519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc ADD share_twitter_text VARCHAR(255) NOT NULL, ADD share_facebook_text VARCHAR(255) NOT NULL, ADD share_email_subject VARCHAR(255) NOT NULL, ADD share_email_body VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc DROP share_twitter_text, DROP share_facebook_text, DROP share_email_subject, DROP share_email_body');
    }
}
