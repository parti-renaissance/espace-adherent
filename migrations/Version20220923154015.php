<?php

namespace Migrations;

use App\Donation\DonationSourceEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220923154015 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations ADD membership TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('UPDATE donations SET membership = :true WHERE source = :source', [
            'true' => true,
            'source' => DonationSourceEnum::MEMBERSHIP,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE donations DROP membership');
    }
}
