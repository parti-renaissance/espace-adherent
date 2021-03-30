<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180619100109 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements CHANGE twitter_text share_twitter_text VARCHAR(255) NOT NULL, CHANGE facebook_text share_facebook_text VARCHAR(255) NOT NULL, CHANGE email_object share_email_subject VARCHAR(255) NOT NULL, CHANGE email_body share_email_body VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mooc_elements CHANGE share_twitter_text twitter_text VARCHAR(255) NOT NULL, CHANGE share_facebook_text facebook_text VARCHAR(255) NOT NULL, CHANGE share_email_subject email_object VARCHAR(255) NOT NULL, CHANGE share_email_body email_body VARCHAR(800) NOT NULL');
    }
}
