<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Report\ReportReasonEnum;
use App\Report\ReportCommand;
use App\Report\ReportCreationCommandHandler;
use App\Report\ReportManager;
use App\Report\ReportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
    #[IsGranted('REPORT')]
    #[Route(path: '/report/{type}/{uuid}', name: 'api_report', requirements: ['type' => ReportType::TYPES_URI_PATTERN, 'uuid' => '%pattern_uuid%'], methods: ['POST'])]
    public function reportAction(
        Request $request,
        string $type,
        string $uuid,
        ReportManager $reportManager,
        ReportCreationCommandHandler $handler,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
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

    #[IsGranted('REPORT')]
    #[Route(path: '/report/reasons', name: 'api_report_reasons', methods: ['GET'])]
    public function reasonsAction(TranslatorInterface $translator): Response
    {
        $reasons = [];
        foreach (ReportReasonEnum::REASONS_LIST as $reason) {
            $reasons[$reason] = $translator->trans($reason, [], 'reports');
        }

        return new JsonResponse($reasons);
    }
}
