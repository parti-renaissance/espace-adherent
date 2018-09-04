<?php

namespace Migrations;

use AppBundle\Entity\Donation;
use AppBundle\Entity\Transaction;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180703113033 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            'UPDATE donations SET donations.status = :status WHERE donations.duration != 0 AND donations.subscription_ended_at IS NULL',
            ['status' => Donation::STATUS_SUBSCRIPTION_IN_PROGRESS]
        );
        $this->addSql(
            'UPDATE donations '.
            'JOIN '.
                '(SELECT MAX(id) max_id, donation_id FROM donation_transactions GROUP BY donation_id) dt_max '.
            'ON (dt_max.donation_id = donations.id) '.
            'JOIN donation_transactions ON (donation_transactions.id = dt_max.max_id) '.
            'SET donations.status = :status '.
            'WHERE donation_transactions.paybox_result_code != :code',
            ['status' => Donation::STATUS_ERROR, 'code' => Transaction::PAYBOX_SUCCESS]
        );
        $this->addSql(
            'UPDATE donations '.
            'LEFT JOIN donation_transactions ON donation_transactions.donation_id = donations.id '.
            'SET donations.status = :status '.
            'WHERE donation_transactions.id IS NULL',
            ['status' => Donation::STATUS_WAITING_CONFIRMATION]
        );
        $this->addSql(
            'UPDATE donations '.
            'JOIN '.
                '(SELECT MAX(id) max_id, donation_id FROM donation_transactions GROUP BY donation_id) dt_max '.
            'ON (dt_max.donation_id = donations.id) '.
            'JOIN donation_transactions ON (donation_transactions.id = dt_max.max_id) '.
            'SET donations.status = :status '.
            'WHERE (donations.duration = 0 OR (donations.duration > 0 AND donations.subscription_ended_at IS NOT NULL)) '.
            'AND donation_transactions.paybox_result_code = :code',
            ['status' => Donation::STATUS_FINISHED, 'code' => Transaction::PAYBOX_SUCCESS]
        );
        $this->addSql(
            'UPDATE donations SET donations.status = :status '.
            'WHERE donations.duration != 0 '.
            'AND donations.subscription_ended_at IS NOT NULL '.
            'AND donations.status != :finished',
            ['status' => Donation::STATUS_CANCELED, 'finished' => Donation::STATUS_FINISHED]
        );
    }

    public function down(Schema $schema): void
    {
    }
}
