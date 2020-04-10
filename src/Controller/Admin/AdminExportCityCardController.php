<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Admin\Election\CityCardAdmin;
use AppBundle\Election\CityResultAggregator;
use AppBundle\Election\VoteListNuanceEnum;
use AppBundle\Entity\Election\CityCard;
use AppBundle\Utils\PhpConfigurator;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Source\ArraySourceIterator;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminExportCityCardController extends Controller
{
    private const NUANCES_TO_EXPORT = [
        VoteListNuanceEnum::REM,
        VoteListNuanceEnum::MDM,
        VoteListNuanceEnum::RN,
        VoteListNuanceEnum::SOC,
        VoteListNuanceEnum::LR,
        VoteListNuanceEnum::VEC,
    ];

    private const NUANCES_CENTER = [
        VoteListNuanceEnum::REM,
        VoteListNuanceEnum::MDM,
        VoteListNuanceEnum::UDI,
        VoteListNuanceEnum::UC,
        VoteListNuanceEnum::DVC,
    ];

    private $aggregator;
    private $exporter;
    private $admin;

    public function __construct(CityResultAggregator $aggregator, Exporter $exporter, CityCardAdmin $admin)
    {
        $this->aggregator = $aggregator;
        $this->exporter = $exporter;
        $this->admin = $admin;
    }

    /**
     * @Route("/app/election-citycard/export-all.{_format}", name="admin_city_card_export_all", methods={"GET"}, defaults={"_format": "xls"}, requirements={"_format": "csv|xls"})
     *
     * @Security("is_granted('ROLE_ADMIN_ELECTION_CITY_CARD')")
     */
    public function exportCityCardsAction(Request $request, string $_format): Response
    {
        PhpConfigurator::disableMemoryLimit();

        return $this->exporter->getResponse(
            $_format,
            'export-villes--'.date('d-m-Y--H-i').'.'.$_format,
            new IteratorCallbackSourceIterator($this->getCityCardIterator($request), function (array $cityCard) {
                /** @var CityCard $cityCard */
                $cityCard = $cityCard[0];
                $city = $cityCard->getCity();
                $results = $this->aggregator->getResults($cityCard->getCity());

                $moreThan10Percent = 0;
                $lessThan10Percent = 0;
                $lessThan5Percent = 0;
                $laremPositions = [];
                $hasWinningList = false;
                $centerLessThan5Percent = null;
                $centerBetween5and10Percent = null;
                $centerMoreThan10Percent = null;

                foreach ($results->getLists() as $list) {
                    if (10 <= $list['percent']) {
                        ++$moreThan10Percent;
                    } else {
                        ++$lessThan10Percent;
                    }

                    if (5 > $list['percent']) {
                        ++$lessThan5Percent;
                    }

                    if (50 <= $list['percent']) {
                        $hasWinningList = true;
                    }

                    if (VoteListNuanceEnum::REM === $list['nuance']) {
                        $laremPositions[] = $list['place'];
                    }

                    if (\in_array($list['nuance'], self::NUANCES_CENTER, true)) {
                        if (10 <= $list['percent']) {
                            $centerMoreThan10Percent = true;
                            $centerBetween5and10Percent = false;
                            $centerLessThan5Percent = false;
                        } elseif (!$centerMoreThan10Percent && 5 <= $list['percent'] && 10 > $list['percent']) {
                            $centerBetween5and10Percent = true;
                            $centerLessThan5Percent = false;
                        } elseif (!$centerMoreThan10Percent && !$centerBetween5and10Percent) {
                            $centerLessThan5Percent = true;
                        }
                    }
                }

                $row = [
                    'INSEE' => $city->getInseeCode(),
                    'Commune' => $city->getName(),
                    'Département' => $city->getDepartment()->getName(),
                    'Région' => $city->getDepartment()->getRegion()->getName(),
                    'Population' => $cityCard->getPopulation(),
                    'Nb listes élu T1' => $moreThan10Percent,
                    'Nb listes inf 10%' => $lessThan10Percent,
                    'Nb listes inf 5%' => $lessThan5Percent,
                    'Position LaREM' => implode(', ', $laremPositions),
                    'Liste >50%' => $hasWinningList ? 'Oui' : 'Non',
                    'Centre <5%' => $centerLessThan5Percent ? 'Oui' : 'Non',
                    'Centre entre 5% (inclus) et 10% (exclus)' => $centerBetween5and10Percent ? 'Oui' : 'Non',
                    'Centre >= 10%' => $centerMoreThan10Percent ? 'Oui' : 'Non',
                ];

                foreach (self::NUANCES_TO_EXPORT as $nuance) {
                    $lessThan5Percent = null;
                    $between5and10Percent = null;
                    $moreThan10Percent = null;
                    $place = null;

                    if ($list = $results->getList($nuance)) {
                        $lessThan5Percent = (5 > $list['percent']) ? 'Oui' : 'Non';
                        $between5and10Percent = (5 <= $list['percent'] && 10 > $list['percent']) ? 'Oui' : 'Non';
                        $moreThan10Percent = (10 <= $list['percent']) ? 'Oui' : 'Non';
                        $place = $list['place'];
                    }

                    $row = array_merge($row, [
                        "$nuance <5%" => $lessThan5Percent,
                        "$nuance entre 5% (inclus) et 10% (exclus)" => $between5and10Percent,
                        "$nuance >= 10%" => $moreThan10Percent,
                        "Classement $nuance au T1" => $place,
                    ]);
                }

                return $row;
            })
        );
    }

    /**
     * @Route("/app/election-citycard/export-lists.{_format}", name="admin_city_card_export_lists", methods={"GET"}, defaults={"_format": "xls"}, requirements={"_format": "csv|xls"})
     *
     * @Security("is_granted('ROLE_ADMIN_ELECTION_CITY_CARD')")
     */
    public function exportVoteResultListsAction(Request $request, string $_format): Response
    {
        PhpConfigurator::disableMemoryLimit();

        $rows = [];

        foreach ($this->getCityCardIterator($request) as $cityCard) {
            /** @var CityCard $cityCard */
            $cityCard = $cityCard[0];
            $city = $cityCard->getCity();
            $results = $this->aggregator->getResults($cityCard->getCity());

            $commonColumns = [
                'INSEE' => $city->getInseeCode(),
                'Commune' => $city->getName(),
                'Département' => $city->getDepartment()->getName(),
                'Région' => $city->getDepartment()->getRegion()->getName(),
                'Population' => $cityCard->getPopulation(),
            ];

            foreach ($results->getLists() as $list) {
                $rows[] = $commonColumns + [
                    'Liste' => $list['name'],
                    'Etiquette' => $list['nuance'],
                    'Résultat' => $list['percent'].'%',
                    'Panneau list' => $list['position'],
                    'Candidat' => $list['candidate'],
                ];
            }
        }

        return $this->exporter->getResponse(
            $_format,
            'export-listes--'.date('d-m-Y--H-i').'.'.$_format,
            new ArraySourceIterator($rows)
        );
    }

    private function getCityCardIterator(Request $request): \Iterator
    {
        $this->admin->setRequest($request);
        $datagrid = $this->admin->getDatagrid();
        $datagrid->buildPager();

        /** @var QueryBuilder|ProxyQueryInterface $query */
        $query = $datagrid->getQuery();

        return $query
            ->select('DISTINCT '.$alias = current($query->getRootAliases()))
            ->addSelect('city', 'department', 'region')
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->getQuery()
            ->iterate()
        ;
    }
}
