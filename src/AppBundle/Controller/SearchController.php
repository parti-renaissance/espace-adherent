<?php

namespace AppBundle\Controller;

use AppBundle\Search\SearchParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    /**
     * @Route("/search", name="search_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $search = new SearchParametersFilter();
        $search->handleRequest($request);

        $results = $this->get('app.search.search_results_provider')->find($search);

        return $this->render('search/'.$search->getType().'.html.twig', [
            'search' => $search,
            'results' => $results,
        ]);
    }
}
