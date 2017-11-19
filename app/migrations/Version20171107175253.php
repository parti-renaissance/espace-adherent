<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171107175253 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE groups CHANGE address_country address_country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE procuration_proxies ADD reliability_description VARCHAR(30) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950adc14e7bc9 TO IDX_A956D4E4C14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950ad6bf91e2f TO IDX_A956D4E46BF91E2F');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE groups CHANGE address_country address_country VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e4c14e7bc9 TO IDX_69D950ADC14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e46bf91e2f TO IDX_69D950AD6BF91E2F');
        $this->addSql('ALTER TABLE procuration_proxies DROP reliability_description');
    }
}
