<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportReasonEnum;
use AppBundle\Report\ReportCommand;
use AppBundle\Report\ReportCreationCommandHandler;
use AppBundle\Report\ReportManager;
use AppBundle\Report\ReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReportController extends AbstractController
{
    /**
     * @Route(
     *     "/report/{type}/{uuid}",
     *     name="api_report",
     *     requirements={
     *         "type": AppBundle\Report\ReportType::TYPES_URI_PATTERN,
     *         "uuid": "%pattern_uuid%"
     *     }
     * )
     * @Method("POST")
     * @Security("is_granted('REPORT')")
     */
    public function reportAction(
        Request $request,
        string $type,
        string $uuid,
        ReportManager $reportManager,
        ReportCreationCommandHandler $handler,
        Serializer $serializer,
        ValidatorInterface $validator
    ): Response {
        $subject = $reportManager->getSubjectByUuid(ReportType::URI_MAP[$type], $uuid);

        if (!$subject) {
            throw $this->createNotFoundException('Subject of the report cannot be found');
        }

        $reportCommand = new ReportCommand($subject, $this->getUser());

        $serializer->deserialize(
            $request->getContent(),
            ReportCommand::class,
            'json',
            ['object_to_populate' => $reportCommand]
        );

        $violations = $validator->validate($reportCommand);

        if (0 === $violations->count()) {
            $handler->handle($reportCommand);

            return new JsonResponse(null, Response::HTTP_CREATED);
        }

        $errors = $serializer->serialize($violations, 'jsonproblem');

        return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/report/reasons", name="api_report_reasons")
     * @Method("GET")
     * @Security("is_granted('REPORT')")
     */
    public function reasonsAction(TranslatorInterface $translator): Response
    {
        $reasons = [];
        foreach (ReportReasonEnum::REASONS_LIST as $reason) {
            $reasons[$reason] = $translator->trans($reason, [], 'reports');
        }

        return new JsonResponse($reasons);
    }
}
