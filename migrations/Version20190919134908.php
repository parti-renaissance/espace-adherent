<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190919134908 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donation_transactions DROP type');
        $this->addSql('ALTER TABLE donators DROP INDEX donator_unique_matching, ADD INDEX IDX_A902FDD7B08E074EA9D1C132C808BA5A (email_address, first_name, last_name)');
        $this->addSql('ALTER TABLE donators CHANGE email_address email_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations ADD type VARCHAR(255) DEFAULT NULL, CHANGE email_address email_address VARCHAR(255) DEFAULT NULL');
        $this->addSQL('UPDATE donations SET type = \'cb\'');
        $this->addSql('ALTER TABLE donations CHANGE type type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donation_transactions ADD type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE donations DROP type, CHANGE email_address email_address VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE donators DROP INDEX IDX_A902FDD7B08E074EA9D1C132C808BA5A, ADD UNIQUE INDEX donator_unique_matching (email_address, first_name, last_name)');
        $this->addSql('ALTER TABLE donators CHANGE email_address email_address VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
