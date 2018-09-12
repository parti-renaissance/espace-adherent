<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\DonationRepository;
use AppBundle\Statistics\Acquisition\Calculator\Category\DonationCategoryTrait;
use AppBundle\Statistics\Acquisition\StatisticsRequest;
use Doctrine\ORM\Query\Expr\Join;

abstract class AbstractDonationCalculator extends AbstractCalculator
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
                return $total += $totalByMonth;
            },
            $this->fillEmptyCase($this->getNewCounters($request), $keys)
        );
    }

    private function getTotalInitial(StatisticsRequest $request): int
    {
        $qb = $this->repository
            ->createQueryBuilder('donation')
            ->select('COUNT(1) AS total')
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
        ;

        if ($this->isAdherentOnly()) {
            $qb
                ->innerJoin(Adherent::class, 'adherent', Join::WITH, 'adherent.emailAddress = donation.emailAddress')
                ->andWhere('adherent.adherent = true')
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function getNewCounters(StatisticsRequest $request): array
    {
        $qb = $this->repository
            ->createQueryBuilder('donation')
            ->select('COUNT(1) AS total')
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
        ;

        if ($this->isAdherentOnly()) {
            $qb
                ->innerJoin(Adherent::class, 'adherent', Join::WITH, 'adherent.emailAddress = donation.emailAddress')
                ->andWhere('adherent.adherent = true')
            ;
        }

        return $qb->getQuery()->getArrayResult();
    }

    protected function isAdherentOnly(): bool
    {
        return false;
    }

    abstract protected function getDonationStatus(): string;

    abstract protected function getDonationDuration(): int;
}
