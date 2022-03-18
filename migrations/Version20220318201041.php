<?php

namespace Migrations;

use App\QrCode\QrCodeHostEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220318201041 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE qr_code ADD host VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE qr_code SET host = :host', ['host' => QrCodeHostEnum::HOST_ENMARCHE]);
        $this->addSql('ALTER TABLE qr_code CHANGE host host VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE qr_code DROP host');
    }
}
