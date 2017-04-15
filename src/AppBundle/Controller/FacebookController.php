<?php

namespace AppBundle\Controller;

use AppBundle\Entity\FacebookProfile;
use Facebook\Exceptions\FacebookSDKException;
use Imagine\Image\Point;
use Ramsey\Uuid\Uuid;
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
    public function authAction(): RedirectResponse
    {
        $fb = $this->get('app.facebook.api');
        $redirectUrl = str_replace('http://', 'https://', $this->generateUrl('app_facebook_user_id', [], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->redirect($fb->getRedirectLoginHelper()->getLoginUrl($redirectUrl, ['public_profile', 'email']));
    }

    /**
     * @Route("/import", name="app_facebook_user_id")
     * @Method("GET")
     */
    public function getUserIdAction(Request $request): RedirectResponse
    {
        if (!$request->query->has('code')) {
            if ('access_denied' === $request->query->get('error')) {
                $this->addFlash('info', 'Pour habiller votre photo aux couleurs d\'En Marche, vous devez nous autoriser à télécharger votre image de profil.');
            }

            return $this->redirectToRoute('app_facebook_index');
        }

        $fb = $this->get('app.facebook.api');
        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookSDKException $exception) {
            return $this->redirectToRoute('app_facebook_auth');
        }

        $response = $fb->get('/me?fields=id,email,name,age_range,gender', $accessToken->getValue())->getDecodedBody();

        $repository = $this->getDoctrine()->getRepository(FacebookProfile::class);
        $fbProfile = $repository->persistFromSDKResponse($response);

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
        $fbProfile = null;

        if (Uuid::isValid($uuid = $request->query->get('uuid'))) {
            $repository = $this->getDoctrine()->getRepository(FacebookProfile::class);
            $fbProfile = $repository->findOneBy(['uuid' => $uuid]);
        }

        if (!$fbProfile) {
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
                        'watermark' => $file['filename'],
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
        $fbProfile = null;
        $watermarkNumber = (int) $request->query->get('watermark');

        if (Uuid::isValid($uuid = $request->query->get('uuid'))) {
            $repository = $this->getDoctrine()->getRepository(FacebookProfile::class);
            $fbProfile = $repository->findOneBy(['uuid' => $uuid]);
        }

        if (!$fbProfile || !$watermarkNumber) {
            throw $this->createNotFoundException();
        }

        $storage = $this->get('app.storage');
        if (!$storage->has('static/watermarks/'.$watermarkNumber.'.png')) {
            throw $this->createNotFoundException();
        }

        $imagine = $this->get('app.imagine');

        $fbApiHost = $this->getParameter('env(FACEBOOK_GRAPH_API_HOST)');
        $pictureUrl = sprintf('%s/%s/picture?width=1500', $fbApiHost, $fbProfile->getFacebookId());
        $picture = $imagine->open($pictureUrl);

        $watermark = $imagine->load($storage->read('static/watermarks/'.$watermarkNumber.'.png'));

        $watermark->resize($picture->getSize());
        $picture->paste($watermark, new Point(0, 0));

        return new Response(base64_encode($picture->get('jpeg')));
    }
}
