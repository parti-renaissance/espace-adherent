<?php

namespace App\Membership;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Cake\Chronos\Chronos;

class AdherentManager
{
    public function __construct(private readonly AdherentRepository $repository)
    {
    }

    public function countMembersByMonthManagedBy(Adherent $referent, int $months = 6): array
    {
        $countByMonth = [];

        foreach (range(0, $months - 1) as $monthInterval) {
            $until = $monthInterval
                        ? (new Chronos("last day of -$monthInterval month"))->setTime(23, 59, 59, 999)
                        : new Chronos();

            $count = $this->repository->countMembersManagedBy($referent, $until);

            $countByMonth[] = ['date' => $until->format('Y-m'), 'total' => $count];
        }

        return $countByMonth;
    }
}
