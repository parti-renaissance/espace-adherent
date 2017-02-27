<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Committee;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class CommitteesController extends Controller
{
    /**
     * @Route("/committees", name="api_committees")
     * @Method("GET")
     */
    public function indexAction()
    {
        $data = [];
        $committees = $this->getDoctrine()->getRepository(Committee::class)->findApprovedCommittees();

        foreach ($committees as $committee) {
            if (!$committee->getLatitude() || !$committee->getLongitude()) {
                continue;
            }

            $data[] = [
                'uuid' => $committee->getUuid()->toString(),
                'slug' => $committee->getSlug(),
                'name' => $committee->getName(),
                'position' => [
                    'lat' => (float) $committee->getLatitude(),
                    'lng' => (float) $committee->getLongitude(),
                ],
            ];
        }

        return new JsonResponse($data);
    }
}
