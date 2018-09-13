<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Repository\DonationRepository;
use AppBundle\Statistics\Acquisition\Calculator\Category\DonationCategoryTrait;
use AppBundle\Statistics\Acquisition\StatisticsRequest;

abstract class AbstractAmountDonationCalculator extends AbstractCalculator
{
    use DonationCategoryTrait;

    private $repository;

    public function __construct(DonationRepository $repository)
    {
        $this->repository = $repository;
    }

    protected function processing(StatisticsRequest $request, array $keys): array
    {
        $total = $this->getTotalInitial($request);

        return array_map(
            function (int $totalByMonth) use (&$total) {
                $total += $totalByMonth;

                return round($total / 100, 2);
            },
            $this->fillEmptyCase($this->getNewCounters($request), $keys)
        );
    }

    private function getTotalInitial(StatisticsRequest $request): int
    {
        return (int) $this->repository
            ->createQueryBuilder('donation')
            ->select('SUM(donation.amount) AS total')
            ->where('donation.createdAt < :date')
            ->andWhere('donation.status = :status')
            ->andWhere('donation.duration = :duration')
            ->andWhere('(donation.postAddress.country IN (:tags) OR donation.postAddress.postalCode IN (:tags))')
            ->setParameters([
                'date' => $request->getStartDateAsString(),
                'status' => $this->getDonationStatus(),
                'duration' => $this->getDonationDuration(),
                'tags' => $request->getTags(),
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function getNewCounters(StatisticsRequest $request): array
    {
        return $this->repository
            ->createQueryBuilder('donation')
            ->select('SUM(donation.amount) AS total')
            ->addSelect('YEAR_MONTH(donation.createdAt) AS date')
            ->where('donation.createdAt >= :start_date AND donation.createdAt <= :end_date')
            ->andWhere('donation.status = :status')
            ->andWhere('donation.duration = :duration')
            ->andWhere('(donation.postAddress.country IN (:tags) OR donation.postAddress.postalCode IN (:tags))')
            ->setParameters([
                'start_date' => $request->getStartDateAsString(),
                'end_date' => $request->getEndDateAsString(),
                'status' => $this->getDonationStatus(),
                'duration' => $this->getDonationDuration(),
                'tags' => $request->getTags(),
            ])
            ->groupBy('date')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    abstract protected function getDonationStatus(): string;

    abstract protected function getDonationDuration(): int;
}
