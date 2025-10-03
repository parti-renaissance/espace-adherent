<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Adherent;
use App\Form\Jecoute\JemarcheDataSurveyFormType;
use App\Jecoute\JemarcheDataSurveyAnswerHandler;
use App\OAuth\Model\DeviceApiUser;
use App\Repository\Jecoute\LocalSurveyRepository;
use App\Repository\Jecoute\NationalSurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')]
#[Route(path: '/jecoute/survey')]
class SurveyController extends AbstractController
{
    #[Route(name: 'api_public_surveys_list', methods: ['GET'])]
    public function surveyListAction(
        #[CurrentUser] Adherent $user,
        LocalSurveyRepository $localSurveyRepository,
        NationalSurveyRepository $nationalSurveyRepository,
    ): Response {
        $surveys = array_merge(
            $localSurveyRepository->findAllByZones($user->getZones()->toArray()),
            $nationalSurveyRepository->findAllPublished()
        );

        usort($surveys, static fn ($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $this->json($surveys, context: ['groups' => ['survey_list']]);
    }

    #[Route(path: '/reply', name: 'api_survey_reply', methods: ['POST'])]
    public function surveyReplyAction(
        Request $request,
        JemarcheDataSurveyAnswerHandler $dataSurveyHandler,
        FormFactoryInterface $formFactory,
    ): JsonResponse {
        /** @var Adherent|DeviceApiUser $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $form = $formFactory->create(JemarcheDataSurveyFormType::class, null, [
            'csrf_protection' => false,
        ]);

        if (!isset($data['dataSurvey'])) {
            $data['dataSurvey']['survey'] = $data['survey'];
            unset($data['survey']);
            $data['dataSurvey']['answers'] = $data['answers'];
            unset($data['answers']);
        }

        if (isset($data['type'])) {
            unset($data['type']);
        }

        $form->submit($data);

        if (!$form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $this->getFormErrors($form),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($user instanceof Adherent) {
            $dataSurveyHandler->handle($form->getData(), $user);
        } elseif ($user instanceof DeviceApiUser) {
            $dataSurveyHandler->handleForDevice($form->getData(), $user->getDevice());
        }

        return new JsonResponse(['status' => 'ok'], JsonResponse::HTTP_CREATED);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getFormErrors($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
