<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191030140739 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donators ADD gender VARCHAR(6) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE donators SET gender = (
                SELECT gender
                FROM donations d
                WHERE d.donator_id = id
                LIMIT 1 
            )
SQL
        );
        $this->addSql('DROP INDEX donation_email_idx ON donations');
        $this->addSql('ALTER TABLE donations DROP gender, DROP email_address, DROP first_name, DROP last_name, CHANGE donator_id donator_id INT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations ADD gender VARCHAR(6) NOT NULL COLLATE utf8_unicode_ci, ADD email_address VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD first_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, ADD last_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, CHANGE donator_id donator_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('CREATE INDEX donation_email_idx ON donations (email_address)');
        $this->addSql('ALTER TABLE donators DROP gender');
    }
}
