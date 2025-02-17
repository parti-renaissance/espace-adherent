<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Adherent;
use App\Form\Jecoute\JemarcheDataSurveyFormType;
use App\Jecoute\JemarcheDataSurveyAnswerHandler;
use App\OAuth\Model\DeviceApiUser;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\LocalSurveyRepository;
use App\Repository\Jecoute\NationalSurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("(is_granted('ROLE_USER') or is_granted('ROLE_OAUTH_DEVICE')) and (is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS') or is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP'))"))]
#[Route(path: '/jecoute/survey')]
class SurveyController extends AbstractController
{
    #[Route(name: 'api_public_surveys_list', methods: ['GET'])]
    public function surveyListAction(
        Request $request,
        LocalSurveyRepository $localSurveyRepository,
        NationalSurveyRepository $nationalSurveyRepository,
        ZoneRepository $zoneRepository,
    ): Response {
        $postalCode = null;
        /** @var Adherent|DeviceApiUser $user */
        $user = $this->getUser();

        if ($user instanceof DeviceApiUser) {
            if (!$postalCode = $request->get('postalCode')) {
                return $this->json(['error' => 'Parameter "postalCode" missing when using a Device token.'], 400);
            }

            if (!preg_match('/\d{5}/', $postalCode)) {
                return $this->json(['error' => 'Parameter "postalCode" must be 5 numbers.'], 400);
            }
        }

        if ($user instanceof Adherent) {
            $zones = $user->getZones()->toArray();
        } else {
            $zones = $zoneRepository->findByPostalCode($postalCode);
        }

        $localSurveys = $localSurveyRepository->findAllByZones($zones);

        return $this->json(
            array_merge(
                $localSurveys,
                $nationalSurveyRepository->findAllPublished()
            ),
            context: ['groups' => ['survey_list']]
        );
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
