<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Admin\Election\CityCardAdmin;
use AppBundle\Election\CityResultAggregator;
use AppBundle\Election\VoteListNuanceEnum;
use AppBundle\Entity\Election\CityCard;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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

                foreach ($results->getLists() as $list) {
                    if (10 <= $list['percent']) {
                        ++$moreThan10Percent;
                    } else {
                        ++$lessThan10Percent;
                    }

                    if (5 > $list['percent']) {
                        ++$lessThan5Percent;
                    }

                    if (VoteListNuanceEnum::REM === $list['nuance']) {
                        $laremPositions[] = $list['place'];
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
                    'Position LaREM' => implode($laremPositions, ', '),
                ];

                foreach (self::NUANCES_TO_EXPORT as $nuance) {
                    $list = $results->getList($nuance);

                    if (!$list) {
                        continue;
                    }

                    $lessThan5Percent = (5 > $list['percent']);
                    $between5and10Percent = (5 <= $list['percent'] && 10 > $list['percent']);
                    $moreThan10Percent = (10 <= $list['percent']);

                    $row = array_merge($row, [
                        "$nuance <5%" => $lessThan5Percent ? 'Oui' : 'Non',
                        "$nuance entre 5% (inclus) et 10% (exclus)" => $between5and10Percent ? 'Oui' : 'Non',
                        "$nuance >= 10%" => $moreThan10Percent ? 'Oui' : 'Non',
                        "Classement $nuance au T1" => $list['place'],
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
        $rows = [];

        foreach ($this->getCityCardIterator($request) as $cityCard) {
            /** @var CityCard $cityCard */
            $cityCard = $cityCard[0];
            $city = $cityCard->getCity();
            $results = $this->aggregator->getResults($cityCard->getCity());

            $communColumns = [
                'INSEE' => $city->getInseeCode(),
                'Commune' => $city->getName(),
                'Département' => $city->getDepartment()->getName(),
                'Région' => $city->getDepartment()->getRegion()->getName(),
                'Population' => $cityCard->getPopulation(),
            ];

            foreach ($results->getLists() as $list) {
                $rows[] = array_merge($communColumns, [
                    'Liste' => $list['name'],
                    'Etiquette' => $list['nuance'],
                    'Résultat' => $list['percent'].'%',
                    'Panneau list' => $list['position'],
                    'Candidat' => $list['candidate'],
                ]);
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

        $query = $datagrid->getQuery();
        $query->select('DISTINCT '.current($query->getRootAliases()));
        $query->setFirstResult(0);
        $query->setMaxResults(null);

        return $query->getQuery()->iterate();
    }
}
