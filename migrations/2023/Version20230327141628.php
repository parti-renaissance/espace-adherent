<?php

declare(strict_types=1);

namespace Migrations;

use App\ElectedRepresentative\Contribution\ContributionStatusEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230327141628 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          elected_representative
        ADD
          contribution_status VARCHAR(255) DEFAULT NULL,
        ADD
          contributed_at DATETIME DEFAULT NULL');

        $this->addSql('UPDATE elected_representative AS er
          INNER JOIN elected_representative_contribution AS erc
            ON erc.elected_representative_id = er.id
          SET er.contribution_status = :status_eligible,
              er.contributed_at = erc.created_at', [
            'status_eligible' => ContributionStatusEnum::ELIGIBLE,
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative DROP contribution_status, DROP contributed_at');
    }
}
