<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/facebook")
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
     * @Route("/auth", name="app_facebook_auth")
     * @Method("GET")
     */
    public function authAction(): RedirectResponse
    {
        $fb = $this->get('app.facebook.api');
        $helper = $fb->getRedirectLoginHelper();

        return $this->redirect($helper->getLoginUrl($this->generateUrl(
            'app_facebook_user_id',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        )));
    }

    /**
     * @Route("/user/id", name="app_facebook_user_id")
     * @Method("GET")
     */
    public function getUserIdAction(Request $request): RedirectResponse
    {
        if (!$request->query->has('code')) {
            return $this->redirectToRoute('app_facebook_auth');
        }

        $fb = $this->get('app.facebook.api');
        $helper = $fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();

        $response = $fb->get('/me', $accessToken->getValue())->getDecodedBody();

        return $this->redirectToRoute('app_facebook_picture', ['id' => $response['id']]);
    }

    /**
     * @Route("/process/{id}", name="app_facebook_picture")
     * @Method("GET")
     */
    public function processPictureAction($id): Response
    {
        $imageFilter = $this->get('app.image_filter');

        try {
            $base64EncodedPictures = $imageFilter->applyWatermarks(sprintf('%s/%s/picture?type=large', $this->getParameter('env(facebook_graph_api_host)'), $id));
        } catch (\InvalidArgumentException $exception) {
            throw $this->createNotFoundException();
        }

        return $this->render('facebook/show.html.twig', ['base64EncodedPictures' => $base64EncodedPictures]);
    }
}
