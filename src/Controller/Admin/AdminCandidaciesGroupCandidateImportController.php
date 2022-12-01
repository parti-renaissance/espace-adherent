<?php

namespace App\Controller\Admin;

use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\Candidacy;
use App\Form\Admin\LocalElection\CandidateImportType;
use App\LocalElection\CandidateImportCommand;
use App\Repository\LocalElection\CandidacyRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AdminCandidaciesGroupCandidateImportController extends CRUDController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function candidateImportAction(
        Request $request,
        CandidaciesGroup $candidaciesGroup,
        CandidacyRepository $candidacyRepository,
        LoggerInterface $logger
    ): Response {
        $this->admin->checkAccess('candidate_import');

        $form = $this
            ->createForm(CandidateImportType::class, $candidateImportCommand = new CandidateImportCommand())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $candidates = $this->getDataFromFile($candidateImportCommand->getFile()?->getPathname());
            $totalCandidates = \count($candidates);

            $candidatesImported = 0;
            foreach ($candidates as $candidate) {
                if (\array_key_exists('EMAIL', $candidate) && !empty($email = $candidate['EMAIL'])) {
                    try {
                        $existCandidate = $candidacyRepository->findOneByCandidaciesGroupAndEmail($candidaciesGroup, $email);
                        if (!$existCandidate) {
                            $candidacy = new Candidacy();
                            $candidacy->setEmail($email);
                            $candidacy->setGender($this->getFormatedCivility($candidate['GENRE']));
                            $candidacy->setFirstName($candidate['PRENOM'] ?? null);
                            $candidacy->setLastName($candidate['NOM'] ?? null);
                            $candidacy->setPosition($candidate['POSITION'] ?? null);

                            $candidaciesGroup->addCandidacy($candidacy);
                        } else {
                            $existCandidate->setGender($this->getFormatedCivility($candidate['GENRE']));
                            $existCandidate->setFirstName($candidate['PRENOM'] ?? null);
                            $existCandidate->setLastName($candidate['NOM'] ?? null);
                            $existCandidate->setPosition($candidate['POSITION'] ?? null);
                        }

                        ++$candidatesImported;
                    } catch (\Exception $e) {
                        $logger->error(
                            sprintf('L\'importation du candidat %s a échoué. Message: %s',
                            $email,
                            $e->getMessage())
                        );

                        continue;
                    }
                }
            }

            $this->em->flush();
            $this->addFlash('sonata_flash_'.$candidatesImported > 0 ? 'success' : 'error', sprintf('%s sur %s candidat(s) importé(s) dans la liste', $candidatesImported, $totalCandidates));

            return $this->redirect($this->admin->generateObjectUrl('edit', $candidaciesGroup));
        }

        return $this->renderWithExtraParams('admin/local_election/candidate_import.html.twig', [
            'action' => 'candidate_import',
            'object' => $candidateImportCommand,
            'form' => $form->createView(),
        ]);
    }

    private function getDataFromFile(?string $file): array
    {
        if (!$file) {
            return [];
        }

        $decoder = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        return $decoder->decode(file_get_contents($file), 'csv');
    }

    /**
     * @throws \Exception
     */
    private function getFormatedCivility(string $data): string
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

        if (!$validValue) {
            throw new \Exception('Invalid civility');
        }

        return $validValue;
    }
}
