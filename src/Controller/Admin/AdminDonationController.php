<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Donation;
use League\Flysystem\Filesystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/donation")
 *
 * @Security("has_role('ROLE_ADMIN_DONATORS')")
 */
class AdminDonationController extends Controller
{
    /**
     * @Route("/file/{id}", name="app_admin_donation_file", methods="GET")
     */
    public function fileAction(Donation $donation, Filesystem $storage): Response
    {
        $filePath = $donation->getFilePathWithDirectory();

        if (!$storage->has($filePath)) {
            throw $this->createNotFoundException();
        }

        return new Response($storage->read($filePath), Response::HTTP_OK, [
            'Content-Type' => $storage->getMimetype($filePath),
        ]);
    }
}
