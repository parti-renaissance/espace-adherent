<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230705072301 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_contribution DROP FOREIGN KEY FK_6F9C7915D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        ADD
          CONSTRAINT FK_6F9C7915D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE elected_representative_payment DROP FOREIGN KEY FK_4C351AA5D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_payment
        ADD
          CONSTRAINT FK_4C351AA5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_contribution DROP FOREIGN KEY FK_6F9C7915D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_contribution
        ADD
          CONSTRAINT FK_6F9C7915D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE elected_representative_payment DROP FOREIGN KEY FK_4C351AA5D38DA5D3');
        $this->addSql('ALTER TABLE
          elected_representative_payment
        ADD
          CONSTRAINT FK_4C351AA5D38DA5D3 FOREIGN KEY (elected_representative_id) REFERENCES elected_representative (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
