<?php

namespace AppBundle\Controller\EnMarche\ApplicationRequestCandidate;

use AppBundle\ApplicationRequest\ApplicationRequestRepository;
use AppBundle\ApplicationRequest\ApplicationRequestTypeEnum;
use AppBundle\Entity\ApplicationRequest\ApplicationRequest;
use AppBundle\Form\ApplicationRequest\ApplicationRequestTagsType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractApplicationRequestController extends Controller
{
    /**
     * @Route("candidature-colistiers", name="_candidate_running_mate_list", defaults={"type": ApplicationRequestTypeEnum::RUNNING_MATE}, methods={"GET"})
     * @Route("candidature-benevoles", name="_candidate_volunteer_list", defaults={"type": ApplicationRequestTypeEnum::VOLUNTEER}, methods={"GET"})
     */
    public function candidatesListAction(Request $request, ApplicationRequestRepository $repository): Response
    {
        $this->checkAccess();

        return $this->renderTemplate('application_request/space/list.html.twig', [
            'requests' => $this->getApplicationRequests(
                $repository,
                $type = $request->attributes->get('type', ApplicationRequestTypeEnum::RUNNING_MATE)
            ),
            'request_type' => $type,
        ]);
    }

    /**
     * @Route("candidature-colistiers/{uuid}", name="_candidate_running_mate_detail", defaults={"type": ApplicationRequestTypeEnum::RUNNING_MATE}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     * @Route("candidature-benevoles/{uuid}", name="_candidate_volunteer_detail", defaults={"type": ApplicationRequestTypeEnum::VOLUNTEER}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
     */
    public function candidateDetailShowAction(
        ApplicationRequestRepository $repository,
        string $uuid,
        string $type
    ): Response {
        if (!$request = $repository->findOneByUuid($uuid, $type)) {
            $this->createNotFoundException();
        }

        $this->checkAccess($request);

        return $this->renderTemplate('application_request/space/detail.html.twig', [
            'request' => $request,
            'request_type' => $type,
        ]);
    }

    /**
     * @Route("candidature-colistiers/{uuid}/editer-tags", name="_candidate_running_mate_tags", defaults={"type": ApplicationRequestTypeEnum::RUNNING_MATE}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET", "POST"})
     * @Route("candidature-benevoles/{uuid}/editer-tags", name="_candidate_volunteer_tags", defaults={"type": ApplicationRequestTypeEnum::VOLUNTEER}, requirements={"uuid": "%pattern_uuid%"}, methods={"GET", "POST"})
     */
    public function candidateTagsEditAction(
        Request $request,
        ApplicationRequestRepository $repository,
        ObjectManager $manager,
        string $uuid,
        string $type
    ): Response {
        if (!$applicationRequest = $repository->findOneByUuid($uuid, $type)) {
            $this->createNotFoundException();
        }

        $this->checkAccess($applicationRequest);

        $form = $this
            ->createForm(ApplicationRequestTagsType::class, $applicationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('info', 'application_request.tags.edited_successfully');

            return $this->redirectToSpaceRoute('candidate_'.$applicationRequest->getType().'_list');
        }

        return $this->renderTemplate('application_request/space/edit_tags.html.twig', [
            'request_type' => $type,
            'form' => $form->createView(),
        ]);
    }

    abstract protected function getApplicationRequests(ApplicationRequestRepository $repository, string $type): array;

    abstract protected function getSpaceName(): string;

    abstract protected function checkAccess(ApplicationRequest $request = null): void;

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('application_request/space/_base_%s_space.html.twig', $spaceName = $this->getSpaceName()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToSpaceRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_{$this->getSpaceName()}_${subName}", $parameters);
    }
}
