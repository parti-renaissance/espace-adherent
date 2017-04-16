<?php

namespace AppBundle\Controller;

use AppBundle\Entity\FacebookProfile;
use AppBundle\Repository\FacebookProfileRepository;
use Facebook\Exceptions\FacebookSDKException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/profil-facebook")
 */
class FacebookController extends Controller
{
    /**
     * @Route("", name="app_facebook_index")
     * @Method("GET")
     */
    public function indexAction(): Response
    {
        return $this->render('facebook/index.html.twig');
    }

    /**
     * @Route("/connexion", name="app_facebook_auth")
     * @Method("GET")
     */
    public function authAction(Request $request): RedirectResponse
    {
        if ('on' !== $request->query->get('mentions_legales')) {
            $this->addFlash('info', 'Pour habiller votre photo aux couleurs d\'En Marche, vous devez nous autoriser à télécharger votre image de profil.');

            return $this->redirectToRoute('app_facebook_index');
        }

        $fb = $this->get('app.facebook.api');
        $redirectUrl = str_replace('http://', 'https://', $this->generateUrl('app_facebook_user_id', [], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->redirect($fb->getRedirectLoginHelper()->getLoginUrl($redirectUrl, ['public_profile', 'email']));
    }

    /**
     * @Route("/import", name="app_facebook_user_id")
     * @Method("GET")
     */
    public function importAction(Request $request): RedirectResponse
    {
        if (!$request->query->has('code')) {
            if ('access_denied' === $request->query->get('error')) {
                $this->addFlash('info', 'Pour habiller votre photo aux couleurs d\'En Marche, vous devez nous autoriser à télécharger votre image de profil.');
            }

            return $this->redirectToRoute('app_facebook_index');
        }

        try {
            $fbProfile = $this->get('app.facebook.profile_importer')->import();
        } catch (FacebookSDKException $exception) {
            return $this->redirectToRoute('app_facebook_index');
        }

        return $this->redirectToRoute('app_facebook_picture_choose', [
            'uuid' => $fbProfile->getUuid()->toString(),
        ]);
    }

    /**
     * @Route("/choisir-une-image", name="app_facebook_picture_choose")
     * @Method("GET")
     */
    public function choosePictureAction(Request $request): Response
    {
        if (!$fbProfile = $this->getFacebookProfileRepository()->findOneByUuid($request->query->get('uuid'))) {
            $this->addFlash('info', 'Une erreur s\'est produite, pouvez-vous réessayer ?');

            return $this->redirectToRoute('app_facebook_index');
        }

        $router = $this->get('router');
        $uuid = $fbProfile->getUuid()->toString();

        return $this->render('facebook/show.html.twig', [
            'urls' => array_map(
                function ($file) use ($router, $uuid) {
                    return $router->generate('app_facebook_picture_build', [
                        'uuid' => $uuid,
                        'filter' => $file['filename'],
                    ]);
                },
                $this->get('app.storage')->listContents('static/watermarks')
            ),
        ]);
    }

    /**
     * @Route("/build", name="app_facebook_picture_build")
     * @Method("GET")
     */
    public function buildPictureAction(Request $request): Response
    {
        if (!$fbProfile = $this->getFacebookProfileRepository()->findOneByUuid($request->query->get('uuid'))) {
            throw $this->createNotFoundException();
        }

        if (!$filterNumber = (int) $request->query->get('filter')) {
            throw $this->createNotFoundException();
        }

        $storage = $this->get('app.storage');
        if (!$storage->has('static/watermarks/'.$filterNumber.'.png')) {
            throw $this->createNotFoundException();
        }

        $pictureData = $this->get('app.facebook.picture_importer')->import($fbProfile->getFacebookId());
        $filterData = $storage->read('static/watermarks/'.$filterNumber.'.png');

        if (!$filteredPictureData = $this->get('app.facebook.picture_filterer')->applyFilter($pictureData, $filterData)) {
            throw $this->createNotFoundException();
        }

        return new Response(base64_encode($filteredPictureData));
    }

    private function getFacebookProfileRepository(): FacebookProfileRepository
    {
        return $this->getDoctrine()->getRepository(FacebookProfile::class);
    }
}
