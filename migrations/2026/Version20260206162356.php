<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260206162356 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE procuration_v2_elections TO procuration_elections');
        $this->addSql('RENAME TABLE procuration_v2_initial_requests TO procuration_initial_requests');
        $this->addSql('RENAME TABLE procuration_v2_matching_history TO procuration_matching_history');
        $this->addSql('RENAME TABLE procuration_v2_proxies TO procuration_proxies');
        $this->addSql('RENAME TABLE procuration_v2_proxy_action TO procuration_proxy_action');
        $this->addSql('RENAME TABLE procuration_v2_proxy_slot TO procuration_proxy_slot');
        $this->addSql('RENAME TABLE procuration_v2_proxy_slot_action TO procuration_proxy_slot_action');
        $this->addSql('RENAME TABLE procuration_v2_request_action TO procuration_request_action');
        $this->addSql('RENAME TABLE procuration_v2_request_slot TO procuration_request_slot');
        $this->addSql('RENAME TABLE procuration_v2_request_slot_action TO procuration_request_slot_action');
        $this->addSql('RENAME TABLE procuration_v2_requests TO procuration_requests');
        $this->addSql('RENAME TABLE procuration_v2_rounds TO procuration_rounds');

        $this->addSql('ALTER TABLE procuration_elections RENAME INDEX uniq_b8544e75e237e06 TO UNIQ_ED87A5435E237E06');
        $this->addSql('ALTER TABLE procuration_elections RENAME INDEX uniq_b8544e7989d9b62 TO UNIQ_ED87A543989D9B62');
        $this->addSql('ALTER TABLE procuration_elections RENAME INDEX uniq_b8544e7d17f50a6 TO UNIQ_ED87A543D17F50A6');
        $this->addSql('ALTER TABLE procuration_elections RENAME INDEX idx_b8544e79df5350c TO IDX_ED87A5439DF5350C');
        $this->addSql('ALTER TABLE procuration_elections RENAME INDEX idx_b8544e7cf1918ff TO IDX_ED87A543CF1918FF');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_initial_requests RENAME INDEX uniq_4bf11906d17f50a6 TO UNIQ_ACD96BD8D17F50A6
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_initial_requests RENAME INDEX idx_4bf1190625f06c53 TO IDX_ACD96BD825F06C53
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_matching_history RENAME INDEX idx_4b792213427eb8a5 TO IDX_AC5150CD427EB8A5
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_matching_history RENAME INDEX idx_4b792213db26a4e TO IDX_AC5150CDDB26A4E
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_matching_history RENAME INDEX idx_4b792213a6005ca0 TO IDX_AC5150CDA6005CA0
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_matching_history RENAME INDEX idx_4b792213f38cba7c TO IDX_AC5150CDF38CBA7C
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_matching_history RENAME INDEX idx_4b7922133bb21cf9 TO IDX_AC5150CD3BB21CF9
            SQL);
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX uniq_4d04eba4d17f50a6 TO UNIQ_9B5E777AD17F50A6');
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX idx_4d04eba49df5350c TO IDX_9B5E777A9DF5350C');
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX idx_4d04eba4cf1918ff TO IDX_9B5E777ACF1918FF');
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX idx_4d04eba4149e6033 TO IDX_9B5E777A149E6033');
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX idx_4d04eba4f3f90b30 TO IDX_9B5E777AF3F90B30');
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX idx_4d04eba425f06c53 TO IDX_9B5E777A25F06C53');
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX idx_4d04eba47b00651c TO IDX_9B5E777A7B00651C');
        $this->addSql('ALTER TABLE procuration_proxies RENAME INDEX idx_4d04eba48b8e8428 TO IDX_9B5E777A8B8E8428');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_action RENAME INDEX uniq_b35088fd17f50a6 TO UNIQ_7C2F5E54D17F50A6
            SQL);
        $this->addSql('ALTER TABLE procuration_proxy_action RENAME INDEX idx_b35088fdb26a4e TO IDX_7C2F5E54DB26A4E');
        $this->addSql('ALTER TABLE procuration_proxy_action RENAME INDEX idx_b35088ff675f31b TO IDX_7C2F5E54F675F31B');
        $this->addSql('ALTER TABLE procuration_proxy_action RENAME INDEX idx_b35088f9301170 TO IDX_7C2F5E549301170');
        $this->addSql('ALTER TABLE procuration_proxy_slot RENAME INDEX uniq_87509068d17f50a6 TO UNIQ_560DF578D17F50A6');
        $this->addSql('ALTER TABLE procuration_proxy_slot RENAME INDEX idx_875090689df5350c TO IDX_560DF5789DF5350C');
        $this->addSql('ALTER TABLE procuration_proxy_slot RENAME INDEX idx_87509068cf1918ff TO IDX_560DF578CF1918FF');
        $this->addSql('ALTER TABLE procuration_proxy_slot RENAME INDEX idx_87509068db26a4e TO IDX_560DF578DB26A4E');
        $this->addSql('ALTER TABLE procuration_proxy_slot RENAME INDEX idx_87509068a6005ca0 TO IDX_560DF578A6005CA0');
        $this->addSql('ALTER TABLE procuration_proxy_slot RENAME INDEX idx_8750906810dbbec4 TO IDX_560DF57810DBBEC4');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot_action RENAME INDEX uniq_535d3e99d17f50a6 TO UNIQ_32D1E938D17F50A6
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot_action RENAME INDEX idx_535d3e994fccd8f9 TO IDX_32D1E9384FCCD8F9
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot_action RENAME INDEX idx_535d3e99f675f31b TO IDX_32D1E938F675F31B
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_proxy_slot_action RENAME INDEX idx_535d3e999301170 TO IDX_32D1E9389301170
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_action RENAME INDEX uniq_24e294abd17f50a6 TO UNIQ_C4260BA4D17F50A6
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_action RENAME INDEX idx_24e294ab427eb8a5 TO IDX_C4260BA4427EB8A5
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_action RENAME INDEX idx_24e294abf675f31b TO IDX_C4260BA4F675F31B
            SQL);
        $this->addSql('ALTER TABLE procuration_request_action RENAME INDEX idx_24e294ab9301170 TO IDX_C4260BA49301170');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot RENAME INDEX uniq_da56a35fd17f50a6 TO UNIQ_AD4CF584D17F50A6
            SQL);
        $this->addSql('ALTER TABLE procuration_request_slot RENAME INDEX idx_da56a35f9df5350c TO IDX_AD4CF5849DF5350C');
        $this->addSql('ALTER TABLE procuration_request_slot RENAME INDEX idx_da56a35fcf1918ff TO IDX_AD4CF584CF1918FF');
        $this->addSql('ALTER TABLE procuration_request_slot RENAME INDEX idx_da56a35f427eb8a5 TO IDX_AD4CF584427EB8A5');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot RENAME INDEX uniq_da56a35f4fccd8f9 TO UNIQ_AD4CF5844FCCD8F9
            SQL);
        $this->addSql('ALTER TABLE procuration_request_slot RENAME INDEX idx_da56a35fa6005ca0 TO IDX_AD4CF584A6005CA0');
        $this->addSql('ALTER TABLE procuration_request_slot RENAME INDEX idx_da56a35f10dbbec4 TO IDX_AD4CF58410DBBEC4');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot_action RENAME INDEX uniq_a50f299d17f50a6 TO UNIQ_A5FB59CAD17F50A6
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot_action RENAME INDEX idx_a50f29973c163cb TO IDX_A5FB59CA73C163CB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot_action RENAME INDEX idx_a50f299f675f31b TO IDX_A5FB59CAF675F31B
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  procuration_request_slot_action RENAME INDEX idx_a50f2999301170 TO IDX_A5FB59CA9301170
            SQL);
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX uniq_f6d458cbd17f50a6 TO UNIQ_9769FD84D17F50A6');
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX idx_f6d458cb9df5350c TO IDX_9769FD849DF5350C');
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX idx_f6d458cbcf1918ff TO IDX_9769FD84CF1918FF');
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX idx_f6d458cb149e6033 TO IDX_9769FD84149E6033');
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX idx_f6d458cbf3f90b30 TO IDX_9769FD84F3F90B30');
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX idx_f6d458cb25f06c53 TO IDX_9769FD8425F06C53');
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX idx_f6d458cb7b00651c TO IDX_9769FD847B00651C');
        $this->addSql('ALTER TABLE procuration_requests RENAME INDEX idx_f6d458cb8b8e8428 TO IDX_9769FD848B8E8428');
        $this->addSql('ALTER TABLE procuration_rounds RENAME INDEX uniq_a2ddd28d17f50a6 TO UNIQ_8612EB88D17F50A6');
        $this->addSql('ALTER TABLE procuration_rounds RENAME INDEX idx_a2ddd28a708daff TO IDX_8612EB88A708DAFF');
        $this->addSql('ALTER TABLE procuration_rounds RENAME INDEX idx_a2ddd289df5350c TO IDX_8612EB889DF5350C');
        $this->addSql('ALTER TABLE procuration_rounds RENAME INDEX idx_a2ddd28cf1918ff TO IDX_8612EB88CF1918FF');
        $this->addSql('ALTER TABLE procuration_rounds RENAME INDEX idx_a2ddd28aa9e377a TO IDX_8612EB88AA9E377A');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('RENAME TABLE procuration_elections TO procuration_v2_elections');
        $this->addSql('RENAME TABLE procuration_initial_requests TO procuration_v2_initial_requests');
        $this->addSql('RENAME TABLE procuration_matching_history TO procuration_v2_matching_history');
        $this->addSql('RENAME TABLE procuration_proxies TO procuration_v2_proxies');
        $this->addSql('RENAME TABLE procuration_proxy_action TO procuration_v2_proxy_action');
        $this->addSql('RENAME TABLE procuration_proxy_slot TO procuration_v2_proxy_slot');
        $this->addSql('RENAME TABLE procuration_proxy_slot_action TO procuration_v2_proxy_slot_action');
        $this->addSql('RENAME TABLE procuration_request_action TO procuration_v2_request_action');
        $this->addSql('RENAME TABLE procuration_request_slot TO procuration_v2_request_slot');
        $this->addSql('RENAME TABLE procuration_request_slot_action TO procuration_v2_request_slot_action');
        $this->addSql('RENAME TABLE procuration_requests TO procuration_v2_requests');
        $this->addSql('RENAME TABLE procuration_rounds TO procuration_v2_rounds');
    }
}
