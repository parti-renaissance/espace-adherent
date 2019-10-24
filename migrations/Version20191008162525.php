<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191008162525 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donators ADD reference_donation_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE donators ADD CONSTRAINT FK_A902FDD7ABF665A8 FOREIGN KEY (reference_donation_id) REFERENCES donations (id)');
        $this->addSql('CREATE INDEX IDX_A902FDD7ABF665A8 ON donators (reference_donation_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donators DROP FOREIGN KEY FK_A902FDD7ABF665A8');
        $this->addSql('DROP INDEX IDX_A902FDD7ABF665A8 ON donators');
        $this->addSql('ALTER TABLE donators DROP reference_donation_id');
    }
}
