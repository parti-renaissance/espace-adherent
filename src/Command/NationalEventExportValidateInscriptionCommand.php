<?php

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\NationalEventTypeEnum;
use App\PublicId\AdherentPublicIdGenerator;
use App\PublicId\MeetingInscriptionPublicIdGenerator;
use App\Repository\AdherentRepository;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Sonata\Exporter\Exporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:national-event:export-validate-inscription')]
class NationalEventExportValidateInscriptionCommand extends Command
{
    public function __construct(
        private readonly EventInscriptionRepository $inscriptionRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly Exporter $exporter,
        private readonly HttpClientInterface $nationalEventExportClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $inscriptions = $this->getAllInscriptions();

        if (empty($inscriptions)) {
            $output->writeln('No inscription found');

            return Command::SUCCESS;
        }

        $rows = [];
        $allColumnsAssoc = [];

        foreach ($inscriptions as $inscription) {
            $row = $this->buildRow($inscription);
            $rows[] = $row;
            foreach (array_keys($row) as $k) {
                $allColumnsAssoc[$k] = true;
            }
        }

        $allColumns = array_keys($allColumnsAssoc);

        $normalizedRows = array_map(static function (array $row) use ($allColumns) {
            $out = array_fill_keys($allColumns, null);
            foreach ($row as $k => $v) {
                $out[$k] = $v;
            }

            return $out;
        }, $rows);

        $response = $this->exporter->getResponse('xlsx', 'export.xlsx', new \ArrayIterator($normalizedRows));

        ob_start();
        $response->getCallback()();
        $content = ob_get_clean();

        $this->nationalEventExportClient->request('POST', '', ['json' => [
            'file_name' => 'export-'.(new \DateTime())->format('Y-m-d_His').'.xlsx',
            'file_content' => base64_encode($content),
        ]]);

        return Command::SUCCESS;
    }

    private function buildRow(EventInscription $inscription): array
    {
        $roommateAdherent = null;
        $roommateInscriptions = [];

        if ($inscription->roommateIdentifier) {
            if (preg_match(AdherentPublicIdGenerator::REGEX, $inscription->roommateIdentifier)) {
                if ($roommateAdherent = $this->adherentRepository->findByPublicId($inscription->roommateIdentifier)) {
                    $roommateInscriptions = $this->inscriptionRepository->findAllForAdherent(
                        $roommateAdherent,
                        $inscription->event,
                        [InscriptionStatusEnum::CANCELED, InscriptionStatusEnum::DUPLICATE]
                    );
                }
            } elseif (preg_match(MeetingInscriptionPublicIdGenerator::REGEX, $inscription->roommateIdentifier)) {
                $one = $this->inscriptionRepository->findByPublicId($inscription->roommateIdentifier);
                if ($one) {
                    $roommateInscriptions = [$one];
                }
            }
        }

        $roommateRows = [];

        if ($roommateInscriptions) {
            foreach ($roommateInscriptions as $i => $roommateInscription) {
                $id = $i + 1;

                $roommateRows['Partenaire Public ID '.$id] = $roommateInscription->getPublicId();
                $roommateRows['Partenaire Civilité '.$id] = $roommateInscription->getCivility()->value;
                $roommateRows['Partenaire Prénom '.$id] = $roommateInscription->firstName;
                $roommateRows['Partenaire Nom '.$id] = $roommateInscription->lastName;
                $roommateRows['Partenaire Statut '.$id] = $roommateInscription->status;
                $roommateRows['Partenaire Dpt '.$id] = $roommateInscription->roommateIdentifier;
                $roommateRows['Partenaire Forfait hébergement '.$id] = $roommateInscription->getAccommodationConfig()['titre'] ?? null;
                $roommateRows['Partenaire Code '.$id] = $roommateInscription->roommateIdentifier;
            }
        } elseif ($roommateAdherent) {
            $roommateRows['Partenaire Public ID 1'] = $roommateAdherent->getPublicId();
            $roommateRows['Partenaire Civilité 1'] = $roommateAdherent->getCivility()->value;
            $roommateRows['Partenaire Prénom 1'] = $roommateAdherent->getFirstName();
            $roommateRows['Partenaire Nom 1'] = $roommateAdherent->getLastName();
            $roommateRows['Partenaire Statut 1'] = 'Non inscrit';
            $roommateRows['Partenaire Dpt 1'] = $roommateAdherent->getAssemblyZone()?->getCode();
            $roommateRows['Partenaire Forfait hébergement 1'] = null;
            $roommateRows['Partenaire Code 1'] = null;
        }

        return [
            'Public ID' => $inscription->getPublicId(),
            'Date d\'inscription' => $inscription->getCreatedAt()->format('d/m/Y H:i:s'),
            'Date de dernière modification' => $inscription->getUpdatedAt()->format('d/m/Y H:i:s'),
            'Statut d\'inscription' => $inscription->status,
            'Civilité' => $inscription->getCivility()?->value,
            'Prénom' => $inscription->firstName,
            'Nom' => $inscription->lastName,
            'Date de naissance' => $inscription->birthdate?->format('d/m/Y'),
            'Age' => $inscription->getAge(new \DateTime('2025-09-20 23:59:59')),
            'Forfait transport' => $inscription->getTransportConfig()['titre'] ?? null,
            'Forfait hôtellerie' => $inscription->getAccommodationConfig()['titre'] ?? null,
            'Champ handicap' => $inscription->accessibility,
            'Souhaite être bénévole' => $inscription->volunteer ? 'Oui' : 'Non',
        ] + $roommateRows;
    }

    private function getAllInscriptions(): array
    {
        return $this->inscriptionRepository->createQueryBuilder('i')
            ->addSelect('a')
            ->innerJoin('i.event', 'e')
            ->leftJoin('i.adherent', 'a')
            ->where('i.status IN (:statuses)')
            ->andWhere('e.type = :type')
            ->andWhere('i.accommodation IS NOT NULL AND i.accommodation != \'gratuit\'')
            ->setParameter('statuses', [
                InscriptionStatusEnum::ACCEPTED,
                InscriptionStatusEnum::INCONCLUSIVE,
                InscriptionStatusEnum::REFUSED,
                InscriptionStatusEnum::PENDING,
                InscriptionStatusEnum::IN_VALIDATION,
            ])
            ->setParameter('type', NationalEventTypeEnum::CAMPUS)
            ->orderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
