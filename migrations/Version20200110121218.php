<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200110121218 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE98962831BACAF');
        $this->addSql('DROP INDEX donation_email_idx ON donations');
        $this->addSql('ALTER TABLE donations DROP email_address, DROP first_name, DROP last_name, CHANGE donator_id donator_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE donations ADD CONSTRAINT FK_CDE98962831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE donators DROP INDEX IDX_A902FDD7ABF665A8, ADD UNIQUE INDEX UNIQ_A902FDD7ABF665A8 (reference_donation_id)');
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD7ABF665A8');
        $this->addSql('ALTER TABLE donators ADD last_successful_donation_id INT UNSIGNED DEFAULT NULL, ADD gender VARCHAR(6) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE donators SET gender = (
                SELECT gender
                FROM donations d
                WHERE d.donator_id = id
                LIMIT 1 
            )
SQL
        );
        $this->addSql('ALTER TABLE donations DROP gender');
        $this->addSql('ALTER TABLE donators ADD CONSTRAINT FK_A902FDD7DE59CB1A FOREIGN KEY (last_successful_donation_id) REFERENCES donations (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE donators ADD CONSTRAINT FK_A902FDD7ABF665A8 FOREIGN KEY (reference_donation_id) REFERENCES donations (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A902FDD7DE59CB1A ON donators (last_successful_donation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP FOREIGN KEY FK_CDE98962831BACAF');
        $this->addSql('ALTER TABLE donations ADD gender VARCHAR(6) NOT NULL COLLATE utf8mb4_unicode_ci, ADD email_address VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD first_name VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, ADD last_name VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE donator_id donator_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE donations SET gender = (
                SELECT gender
                FROM donators d
                WHERE d.id = donator_id
                LIMIT 1 
            )
SQL
        );
        $this->addSql('ALTER TABLE donations ADD CONSTRAINT FK_CDE98962831BACAF FOREIGN KEY (donator_id) REFERENCES donators (id)');
        $this->addSql('CREATE INDEX donation_email_idx ON donations (email_address)');
        $this->addSql('ALTER TABLE donators DROP INDEX UNIQ_A902FDD7ABF665A8, ADD INDEX IDX_A902FDD7ABF665A8 (reference_donation_id)');
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD7DE59CB1A');
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD7ABF665A8');
        $this->addSql('DROP INDEX UNIQ_A902FDD7DE59CB1A ON donators');
        $this->addSql('ALTER TABLE donators DROP last_successful_donation_id, DROP gender');
        $this->addSql('ALTER TABLE donators ADD CONSTRAINT FK_A902FDD7ABF665A8 FOREIGN KEY (reference_donation_id) REFERENCES donations (id)');
    }
}
