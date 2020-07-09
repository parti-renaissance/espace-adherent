<?php

namespace App\Controller\EnMarche\Committee;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Repository\CommitteeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractCommitteeController extends Controller
{
    use AccessDelegatorTrait;

    /**
     * @Route("", name="committees")
     */
    public function committeesAction(Request $request, CommitteeRepository $committeeRepository): Response
    {
        return $this->render('referent/committees_list.html.twig', [
            'committees' => $committeeRepository->findReferentCommittees($this->getMainUser($request->getSession())),
            'base_template' => sprintf('committee/_base_%s.html.twig', $spaceName = $this->getSpaceType()),
            'space_name' => $spaceName,
        ]);
    }

    abstract protected function getSpaceType(): string;
}
