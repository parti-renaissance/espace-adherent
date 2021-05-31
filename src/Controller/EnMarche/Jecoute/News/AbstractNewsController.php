<?php

namespace App\Controller\EnMarche\Jecoute\News;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Jecoute\News;
use App\Form\Jecoute\NewsFormType;
use App\Jecoute\NewsHandler;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\NewsRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractNewsController extends AbstractController
{
    use AccessDelegatorTrait;

    protected $newsRepository;
    protected $zoneRepository;

    public function __construct(NewsRepository $newsRepository, ZoneRepository $zoneRepository)
    {
        $this->newsRepository = $newsRepository;
        $this->zoneRepository = $zoneRepository;
    }

    /**
     * @Route("", name="news_list", methods={"GET"})
     */
    public function jecouteNewsListAction(Request $request): Response
    {
        return $this->renderTemplate('jecoute/news/news_list.html.twig', [
            'news' => $this->getNews($this->getMainUser($request->getSession())),
        ]);
    }

    /**
     * @Route(
     *     path="/creer",
     *     name="news_create",
     *     methods={"GET|POST"},
     * )
     */
    public function jecouteNewsCreateAction(
        Request $request,
        ObjectManager $manager,
        NewsHandler $handler,
        UserInterface $user
    ): Response {
        /** @var Adherent $user */
        $news = new News();
        $zones = $this->getZones($this->getMainUser($request->getSession()));
        if (1 === \count($zones)) {
            $news->setZone($zones[0]);
        }

        $options = [
            'zones' => $zones,
        ];

        $form = $this
            ->createForm(NewsFormType::class, $news, $options)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $news->setAuthor($user);
            $news->setSpace($this->getSpaceName());
            $handler->buildTopic($news);

            $manager->persist($news);
            $manager->flush();

            $handler->handleNotification($news);

            $this->addFlash('info', 'jecoute_news.create.success');

            return $this->redirectToNewsRoute('news_list');
        }

        return $this->renderTemplate('jecoute/news/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/{uuid}/editer",
     *     name="news_edit",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"GET|POST"}
     * )
     *
     * @Security("is_granted('IS_AUTHOR_OF', news)")
     */
    public function jecouteNewsEditAction(Request $request, News $news, ObjectManager $manager): Response
    {
        $zones = $this->getZones($this->getMainUser($request->getSession()));

        $options = [
            'zones' => $zones,
            'edit' => true,
        ];

        $form = $this
            ->createForm(NewsFormType::class, $news, $options)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('info', 'jecoute_news.edit.success');

            return $this->redirectToNewsRoute('news_list');
        }

        return $this->renderTemplate('jecoute/news/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/{uuid}/publier",
     *     name="news_publish",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"GET"}
     * )
     *
     * @Security("is_granted('IS_AUTHOR_OF', news) or is_granted('IS_ALLOWED_TO_PUBLISH_JECOUTE_NEWS', news)")
     */
    public function jecouteNewsPublishAction(Request $request, News $news, NewsHandler $handler): Response
    {
        if ($news->isPublished()) {
            throw new BadRequestHttpException('This news is already published.');
        }

        $handler->publish($news);

        $this->addFlash('info', 'jecoute_news.publish.success');

        return $this->redirectToNewsRoute('news_list');
    }

    /**
     * @Route(
     *     path="/{uuid}/depublier",
     *     name="news_unpublish",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"GET"}
     * )
     *
     * @Security("is_granted('IS_AUTHOR_OF', news) or is_granted('IS_ALLOWED_TO_PUBLISH_JECOUTE_NEWS', news)")
     */
    public function jecouteNewsUnpublishAction(Request $request, News $news, NewsHandler $handler): Response
    {
        if (!$news->isPublished()) {
            throw new BadRequestHttpException('This news is already unpublished.');
        }

        $handler->unpublish($news);

        $this->addFlash('info', 'jecoute_news.unpublish.success');

        return $this->redirectToNewsRoute('news_list');
    }

    abstract protected function getSpaceName(): string;

    abstract protected function getZones(Adherent $adherent): array;

    /**
     * @return News[]
     */
    protected function getNews(Adherent $adherent): array
    {
        return $this->newsRepository->listForZone($this->getZones($adherent));
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('jecoute/_base_%s_space.html.twig', $spaceName = $this->getSpaceName()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToNewsRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_jecoute_news_{$this->getSpaceName()}_${subName}", $parameters);
    }
}
