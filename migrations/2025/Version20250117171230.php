<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250117171230 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_request CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE committees CHANGE address_address address_address VARCHAR(255) DEFAULT NULL, CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE events CHANGE address_address address_address VARCHAR(255) DEFAULT NULL, CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_v2_proxies CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_v2_requests CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vox_action CHANGE address_additional_address address_additional_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_request CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE committees CHANGE address_address address_address VARCHAR(150) DEFAULT NULL, CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE `events` CHANGE address_address address_address VARCHAR(150) DEFAULT NULL, CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_v2_proxies CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE procuration_v2_requests CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE vox_action CHANGE address_additional_address address_additional_address VARCHAR(150) DEFAULT NULL');
    }
}
