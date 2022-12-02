<?php

namespace App\Controller\Admin;

use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\Candidacy;
use App\Form\Admin\LocalElection\CandidateImportType;
use App\LocalElection\CandidateImportCommand;
use App\Repository\LocalElection\CandidacyRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCandidaciesGroupCandidateImportController extends CRUDController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function candidateImportAction(
        Request $request,
        CandidaciesGroup $candidaciesGroup,
        CandidacyRepository $candidacyRepository
    ): Response {
        $this->admin->checkAccess('candidate_import');

        $form = $this
            ->createForm(CandidateImportType::class, $candidateImportCommand = new CandidateImportCommand())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $candidateImportCommand->file->getPathname();
            $csv = Reader::createFromPath($file);
            $csv->setHeaderOffset(0);

            $header = $csv->getHeader();
            $records = $csv->getRecords();
            $totalCandidates = iterator_count($records);

            if (!empty($diff = array_diff(['GENRE', 'NOM', 'PRENOM', 'EMAIL', 'POSITION'], $header))) {
                $this->addFlash('sonata_flash_error', sprintf('En-tête de fichier invalide. Il manque la ou les colonne(s) %s', implode(',', $diff)));

                return $this->redirect($this->admin->generateObjectUrl('candidate_import', $candidaciesGroup));
            }

            $candidatesImported = 0;
            foreach ($records as $candidate) {
                if (!empty($email = $candidate['EMAIL'])) {
                    if (!$candidacy = $candidacyRepository->findOneByCandidaciesGroupAndEmail($candidaciesGroup, $email)) {
                        $candidaciesGroup->addCandidacy($candidacy = new Candidacy());
                        $candidacy->setEmail($email);
                    }

                    if (!empty($candidate['GENRE'])) {
                        if (!$civility = $this->getFormattedCivility($candidate['GENRE'])) {
                            $this->addFlash('sonata_flash_error', sprintf('Civilité invalide pour le candidat avec l\'adresse email %s. Veuillez vérifier le fichier en entier', $email));

                            return $this->redirect($this->admin->generateObjectUrl('candidate_import', $candidaciesGroup));
                        }
                        $candidacy->setGender($civility);
                    }

                    if (!empty($candidate['PRENOM'])) {
                        $candidacy->setFirstName($candidate['PRENOM']);
                    }

                    if (!empty($candidate['NOM'])) {
                        $candidacy->setLastName($candidate['NOM']);
                    }

                    if (!empty($candidate['POSITION'])) {
                        $candidacy->setPosition($candidate['POSITION']);
                    }

                    ++$candidatesImported;
                }
            }

            $this->em->flush();
            $this->addFlash('sonata_flash_success', sprintf('%s sur %s candidat(s) importé(s) dans la liste', $candidatesImported, $totalCandidates));

            return $this->redirect($this->admin->generateObjectUrl('edit', $candidaciesGroup));
        }

        return $this->renderWithExtraParams('admin/local_election/candidate_import.html.twig', [
            'action' => 'candidate_import',
            'object' => $candidateImportCommand,
            'form' => $form->createView(),
        ]);
    }

    private function getFormattedCivility(string $data): ?string
    {
        $civilities = [
            Genders::MALE => ['homme', 'monsieur', 'm', 'mr', 'masculin'],
            Genders::FEMALE => ['femme', 'madame', 'mme', 'féminin'],
        ];
        $validValue = null;

        foreach ($civilities as $civility => $values) {
            if (\in_array(mb_strtolower($data), $values)) {
                $validValue = $civility;
                break;
            }
        }

        return $validValue;
    }
}
