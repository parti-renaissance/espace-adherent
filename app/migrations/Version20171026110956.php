<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20171026110956 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE events CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\'');
        $this->addSql('ALTER TABLE procuration_requests ADD processed TINYINT(1) NOT NULL, ADD processed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950adc14e7bc9 TO IDX_A956D4E4C14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_69d950ad6bf91e2f TO IDX_A956D4E46BF91E2F');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE events CHANGE expert_found expert_found TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e4c14e7bc9 TO IDX_69D950ADC14E7BC9');
        $this->addSql('ALTER TABLE order_section_order_article RENAME INDEX idx_a956d4e46bf91e2f TO IDX_69D950AD6BF91E2F');
        $this->addSql('ALTER TABLE procuration_requests DROP processed, DROP processed_at');
    }
}
