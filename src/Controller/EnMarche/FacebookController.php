<?php

namespace App\Controller\EnMarche;

use App\Entity\FacebookProfile;
use App\Exception\BadUuidRequestException;
use App\Exception\InvalidUuidException;
use App\Facebook\PictureFilterer;
use App\Facebook\PictureImporter;
use App\Facebook\PictureUploader;
use App\Facebook\ProfileImporter;
use App\Repository\FacebookProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/profil-facebook")
 */
class FacebookController extends AbstractController
{
    private $storage;
    private $pictureImporter;
    private $pictureFilterer;
    private $repository;

    public function __construct(
        FilesystemInterface $storage,
        PictureImporter $pictureImporter,
        PictureFilterer $pictureFilterer,
        FacebookProfileRepository $repository
    ) {
        $this->storage = $storage;
        $this->pictureImporter = $pictureImporter;
        $this->pictureFilterer = $pictureFilterer;
        $this->repository = $repository;
    }

    /**
     * @Route("", name="app_facebook_index", methods={"GET"})
     */
    public function indexAction(): Response
    {
        return $this->render('facebook/index.html.twig');
    }

    /**
     * @Route("/connexion", name="app_facebook_auth", methods={"GET"})
     */
    public function authAction(Request $request, Facebook $fb): RedirectResponse
    {
        if ('on' !== $request->query->get('mentions_legales')) {
            $this->addFlash('info', 'Pour habiller votre photo aux couleurs d\'En Marche, vous devez nous autoriser à télécharger votre image de profil.');

            return $this->redirectToRoute('app_facebook_index');
        }

        $redirectUrl = $this->generateFacebookRedirectUrl('app_facebook_user_id', []);

        return $this->redirect($fb->getRedirectLoginHelper()->getLoginUrl($redirectUrl, ['public_profile', 'email']));
    }

    /**
     * @Route("/import", name="app_facebook_user_id", methods={"GET"})
     */
    public function importAction(Request $request, ProfileImporter $importer): RedirectResponse
    {
        if (!$request->query->has('code')) {
            if ('access_denied' === $request->query->get('error')) {
                $this->addFlash('info', 'Pour habiller votre photo aux couleurs d\'En Marche, vous devez nous autoriser à télécharger votre image de profil.');
            }

            return $this->redirectToRoute('app_facebook_index');
        }

        try {
            $fbProfile = $importer->import();
        } catch (FacebookSDKException $exception) {
            return $this->redirectToRoute('app_facebook_index');
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        return $this->redirectToRoute('app_facebook_picture_choose', [
            'uuid' => $fbProfile->getUuid()->toString(),
        ]);
    }

    /**
     * @Route("/choisir-une-image", name="app_facebook_picture_choose", methods={"GET"})
     */
    public function choosePictureAction(Request $request): Response
    {
        try {
            if (!$fbProfile = $this->repository->findOneByUuid($request->query->get('uuid'))) {
                $this->addFlash('info', 'Une erreur s\'est produite, pouvez-vous réessayer ?');

                return $this->redirectToRoute('app_facebook_index');
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        $uuid = $fbProfile->getUuid()->toString();

        $urls = [];
        foreach ($this->storage->listContents('static/watermarks') as $filter) {
            $parameters = [
                'uuid' => $uuid,
                'filter' => $filter['filename'],
            ];

            $urls[] = [
                'data' => $this->generateUrl('app_facebook_picture_build', $parameters),
                'upload' => $this->generateUrl('app_facebook_picture_upload_permission', $parameters),
            ];
        }

        return $this->render('facebook/show.html.twig', [
            'urls' => $urls,
        ]);
    }

    /**
     * @Route("/build", name="app_facebook_picture_build", methods={"GET"})
     */
    public function buildPictureAction(Request $request): Response
    {
        try {
            if (!$fbProfile = $this->repository->findOneByUuid($request->query->get('uuid'))) {
                throw $this->createNotFoundException();
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        return new Response(base64_encode($this->buildFilteredPicture($fbProfile, $request)));
    }

    /**
     * @Route("/upload/permission", name="app_facebook_picture_upload_permission", methods={"GET"})
     */
    public function uploadPicturePermissionAction(Request $request, Facebook $fb): Response
    {
        try {
            if (!$fbProfile = $this->repository->findOneByUuid($request->query->get('uuid'))) {
                throw $this->createNotFoundException();
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$filterNumber = (int) $request->query->get('filter')) {
            throw $this->createNotFoundException();
        }

        $redirectUrl = $this->generateFacebookRedirectUrl('app_facebook_picture_upload_execute', [
            'uuid' => $fbProfile->getUuid()->toString(),
            'filter' => $filterNumber,
        ]);

        return $this->redirect($fb->getRedirectLoginHelper()->getLoginUrl($redirectUrl, ['user_photos', 'publish_actions']));
    }

    /**
     * @Route("/upload/executer", name="app_facebook_picture_upload_execute", methods={"GET"})
     */
    public function uploadPictureExecuteAction(
        Request $request,
        PictureUploader $uploader,
        EntityManagerInterface $manager
    ): Response {
        try {
            if (!$fbProfile = $this->repository->findOneByUuid($request->query->get('uuid'))) {
                throw $this->createNotFoundException();
            }

            $filteredPictureData = $this->buildFilteredPicture($fbProfile, $request);
            $response = $uploader->upload($filteredPictureData);

            $fbProfile->logAutoUploaded($response['access_token']);

            $manager->persist($fbProfile);
            $manager->flush();
        } catch (FacebookSDKException $exception) {
            return $this->redirectToRoute('app_facebook_picture_choose', [
                'uuid' => $request->query->get('uuid'),
                'filter' => $request->query->get('filter'),
            ]);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        return $this->redirect('https://www.facebook.com/photo.php?fbid='.$response['photo_id']);
    }

    private function generateFacebookRedirectUrl(string $route, array $parameters)
    {
        $url = $this->generateUrl($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        if ($this->getParameter('kernel.debug')) {
            return $url;
        }

        return str_replace('http://', 'https://', $url);
    }

    private function buildFilteredPicture(FacebookProfile $fbProfile, Request $request): string
    {
        if (!$filterNumber = (int) $request->query->get('filter')) {
            throw $this->createNotFoundException();
        }

        if (!$this->storage->has('static/watermarks/'.$filterNumber.'.png')) {
            throw $this->createNotFoundException();
        }

        $pictureData = $this->pictureImporter->import($fbProfile->getFacebookId());
        $filterData = $this->storage->read('static/watermarks/'.$filterNumber.'.png');

        if (!$filteredPictureData = $this->pictureFilterer->applyFilter($pictureData, $filterData)) {
            throw $this->createNotFoundException();
        }

        return $filteredPictureData;
    }
}
