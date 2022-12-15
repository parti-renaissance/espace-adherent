<?php

namespace App\Controller\Renaissance\LocalElection\Summary;

use App\Entity\LocalElection\CandidaciesGroup;
use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/elections-locales/{id}/profession-de-foi", name="app_renaissance_local_election_summary_faith_statement_download", methods={"GET"})
 */
class DownloadFaithStatementController extends AbstractController
{
    public function __construct(private readonly FilesystemInterface $storage)
    {
    }

    public function __invoke(Request $request, CandidaciesGroup $candidaciesGroup)
    {
        $filePath = $candidaciesGroup->getFaithStatementFilePath();

        if (!$this->storage->has($filePath)) {
            throw $this->createNotFoundException('No file found in storage for this CandidaciesGroup.');
        }

        $response = new Response($this->storage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
        ]);

        if ($request->query->has('download')) {
            $disposition = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $candidaciesGroup->getFaithStatementFilename()
            );

            $response->headers->set('Content-Disposition', $disposition);
        }

        return $response;
    }
}
