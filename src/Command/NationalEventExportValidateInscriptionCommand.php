<?php

namespace App\Command;

use App\Entity\NationalEvent\EventInscription;
use App\NationalEvent\InscriptionStatusEnum;
use App\NationalEvent\NationalEventTypeEnum;
use App\Repository\AdherentRepository;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Sonata\Exporter\Exporter;
use Sonata\Exporter\Source\IteratorCallbackSourceIterator;
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

        $response = $this->exporter->getResponse('xlsx', 'export.xlsx', new IteratorCallbackSourceIterator(new \ArrayIterator($inscriptions), function (EventInscription $inscription) {
            $roommate = $inscription->roommateIdentifier ? $this->adherentRepository->findByPublicId($inscription->roommateIdentifier) : null;

            return [
                'Public ID' => $inscription->getPublicId(),
                'Date d\'inscription' => $inscription->getCreatedAt()->format('d/m/Y H:i:s'),
                'Date de dernière modification' => $inscription->getUpdatedAt()->format('d/m/Y H:i:s'),
                'Statut d\'inscription' => $inscription->status,
                'Civilité' => $inscription->getCivility()?->value,
                'Prénom' => $inscription->firstName,
                'Nom' => $inscription->lastName,
                'Date de naissance' => $inscription->birthdate?->format('d/m/Y'),
                'Forfait transport' => $inscription->getTransportConfig()['titre'] ?? null,
                'Forfait hôtellerie' => $inscription->getAccommodationConfig()['titre'] ?? null,
                'Champ handicap' => $inscription->accessibility,
                'Souhaite être bénévole' => $inscription->volunteer ? 'Oui' : 'Non',
                'Code partenaire' => $inscription->roommateIdentifier,
                'Partenaire Public ID' => $roommate?->getPublicId(),
                'Partenaire Civilité' => $roommate?->getCivility()?->value,
                'Partenaire Prénom' => $roommate?->getFirstName(),
                'Partenaire Nom' => $roommate?->getLastName(),
            ];
        }));

        ob_start();
        $response->getCallback()();
        $content = ob_get_clean();

        $this->nationalEventExportClient->request('POST', '', ['json' => [
            'file_name' => 'export-'.(new \DateTime())->format('Y-m-d_His').'.xlsx',
            'file_content' => base64_encode($content),
        ]]);

        return Command::SUCCESS;
    }

    private function getAllInscriptions(): array
    {
        return $this->inscriptionRepository->createQueryBuilder('i')
            ->addSelect('a')
            ->innerJoin('i.event', 'e')
            ->leftJoin('i.adherent', 'a')
            ->where('i.status IN (:statuses)')
            ->andWhere('e.type = :type')
            ->setParameter('statuses', InscriptionStatusEnum::APPROVED_STATUSES)
            ->setParameter('type', NationalEventTypeEnum::CAMPUS)
            ->orderBy('i.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
