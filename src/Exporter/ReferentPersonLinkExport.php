<?php

namespace App\Exporter;

use App\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ReferentPersonLinkExport
{
    /**
     * @param ReferentPersonLink[] $referentPersonLinks
     */
    public function exportToXlsx(array $referentPersonLinks): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Référent', 'Zones', 'Prénom', 'Nom', 'Statut', 'Email', 'Téléphone', 'Adresse']);
        $sheet->fromArray($this->arrayObjectToArrayValues($referentPersonLinks), null, 'A2');

        return $spreadsheet;
    }

    /**
     * @param ReferentPersonLink[] $referentPersonLinks
     *
     * @return array[]
     */
    private function arrayObjectToArrayValues(array $referentPersonLinks): array
    {
        $result = [];

        foreach ($referentPersonLinks as $referentPersonLink) {
            $tab = [];

            $tab[] = $referentPersonLink->getReferent()->getFullName();
            $tab[] = $referentPersonLink->getReferent()->getAreasToString();
            $tab[] = $referentPersonLink->getFirstName();
            $tab[] = $referentPersonLink->getLastName();
            $tab[] = $referentPersonLink->getPersonOrganizationalChartItem()->getLabel();
            $tab[] = $referentPersonLink->getEmail();
            $tab[] = $referentPersonLink->getPhone();
            $tab[] = $referentPersonLink->getPostalAddress();

            $result[] = $tab;
        }

        return $result;
    }

    public function createResponse(Spreadsheet $spreadsheet): Response
    {
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        $response = new Response(ob_get_contents(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="myfile.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
        ob_end_clean();

        return $response;
    }
}
