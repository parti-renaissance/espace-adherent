<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171023110237 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) DEFAULT NULL, CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) DEFAULT NULL, CHANGE position position VARCHAR(20) DEFAULT NULL, CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950adc14e7bc9 TO IDX_A956D4E4C14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950ad6bf91e2f TO IDX_A956D4E46BF91E2F');
        $this->addSql('ALTER TABLE summaries ADD picture_uploaded TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE adherents CHANGE gender gender VARCHAR(6) NOT NULL COLLATE utf8_unicode_ci, CHANGE position position VARCHAR(20) NOT NULL COLLATE utf8_unicode_ci, CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE committees CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE donations CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE events CHANGE address_country address_country VARCHAR(2) NOT NULL COLLATE utf8_unicode_ci, CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e4c14e7bc9 TO IDX_69D950ADC14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e46bf91e2f TO IDX_69D950AD6BF91E2F');
        $this->addSql('ALTER TABLE summaries DROP picture_uploaded');
    }
}
