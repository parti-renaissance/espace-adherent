<?php

namespace Migrations;

use AppBundle\Entity\Donation;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180502142950 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX donation_uuid_idx ON donations (uuid)');
        $this->addSql('CREATE INDEX donation_email_idx ON donations (email_address)');
        $this->addSql('CREATE INDEX donation_duration_idx ON donations (duration)');
        $this->addSql('ALTER TABLE donations ADD status VARCHAR(25) DEFAULT NULL, MODIFY created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD paybox_order_ref VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX donation_status_idx ON donations (status)');

        $this->addSql(
            'UPDATE donations SET donations.status = :status WHERE donations.finished = 1 AND donations.donated_at IS NOT NULL',
            ['status' => Donation::STATUS_FINISHED]
        );
        $this->addSql(
            'UPDATE donations SET donations.status = :status WHERE donations.finished = 1 AND donations.donated_at IS NULL',
            ['status' => Donation::STATUS_ERROR]
        );
        $this->addSql(
            'UPDATE donations SET donations.status = :status WHERE donations.finished = 0',
            ['status' => Donation::STATUS_WAITING_CONFIRMATION]
        );
        $this->addSql(
            'UPDATE donations SET donations.status = :status WHERE donations.duration != 0 AND donations.donated_at IS NOT NULL',
            ['status' => Donation::STATUS_SUBSCRIPTION_IN_PROGRESS]
        );
        $this->addSql('ALTER TABLE donations CHANGE donated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE donations SET donations.updated_at = donations.created_at WHERE donations.updated_at IS NULL');

        $this->addSql('CREATE TABLE transaction (id INT UNSIGNED AUTO_INCREMENT NOT NULL, donation_id INT UNSIGNED DEFAULT NULL, paybox_result_code VARCHAR(100) DEFAULT NULL, paybox_authorization_code VARCHAR(100) DEFAULT NULL, paybox_payload JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', paybox_date_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', paybox_transaction_id VARCHAR(255) DEFAULT NULL, paybox_subscription_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_723705D15A4036C7 (paybox_transaction_id), INDEX IDX_723705D14DC1279C (donation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE INDEX transaction_result_idx ON transaction (paybox_result_code)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D14DC1279C FOREIGN KEY (donation_id) REFERENCES donations (id)');
        $this->addSql(
            'INSERT INTO transaction (donation_id, paybox_result_code, paybox_authorization_code, paybox_payload, paybox_date_time, paybox_transaction_id, created_at)
                    (SELECT id, paybox_result_code, paybox_authorization_code, paybox_payload,
                      IF (paybox_payload->"$.date" IS NOT NULL AND paybox_payload->"$.time" IS NOT NULL, STR_TO_DATE(
                        CONCAT(
                          SUBSTR(paybox_payload->"$.date", 2, 2),
                          \',\',
                          SUBSTR(paybox_payload->"$.date", 4, 2),
                          \',\',
                          SUBSTR(paybox_payload->"$.date", 6, 4),
                          \' \',
                          SUBSTR(REPLACE(paybox_payload->"$.time", \'%3A\', \':\'), 2, 8)
                        ),
                        \'%d,%m,%Y %H:%i:%s\'
                      ), NULL),
                      IF (paybox_payload->"$.transaction" != \'0\', paybox_payload->"$.transaction", NULL),
                      created_at
                      FROM donations)'
        );
        $this->addSql('ALTER TABLE donations MODIFY status VARCHAR(25) NOT NULL, MODIFY updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE donations DROP paybox_result_code, DROP paybox_authorization_code, DROP paybox_payload, DROP finished');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations ADD paybox_result_code VARCHAR(100) DEFAULT NULL, ADD paybox_authorization_code VARCHAR(100) DEFAULT NULL, ADD paybox_payload JSON DEFAULT NULL COMMENT \'(DC2Type:json_array)\', ADD finished TINYINT(1) NOT NULL, CHANGE updated_at donated_at DATETIME DEFAULT NULL');
        $this->addSql(
            'UPDATE transaction T, donations D
            SET
                D.paybox_payload = T.paybox_payload,
                D.paybox_result_code = T.paybox_result_code,
                D.paybox_authorization_code = T.paybox_authorization_code,
                D.finished = IF (D.status like \'%waiting_confirmation%\', 0, 1),
                D.donated_at = IF (D.status LIKE \'%waiting_confirmation%\' OR D.status LIKE \'%error%\', NULL, D.donated_at)
            WHERE D.id = T.donation_id'
        );
        $this->addSql('DROP INDEX donation_uuid_idx ON donations');
        $this->addSql('DROP INDEX donation_email_idx ON donations');
        $this->addSql('DROP INDEX donation_duration_idx ON donations');
        $this->addSql('DROP INDEX donation_status_idx ON donations');
        $this->addSql('ALTER TABLE donations DROP status, DROP paybox_order_ref');
        $this->addSql('DROP TABLE transaction');
    }
}
