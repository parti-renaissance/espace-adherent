<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\Form\Jecoute\DataSurveyFormType;
use AppBundle\Jecoute\DataSurveyAnswerHandler;
use AppBundle\Jecoute\SurveyTypeEnum;
use AppBundle\Repository\Jecoute\LocalSurveyRepository;
use AppBundle\Repository\Jecoute\NationalSurveyRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/jecoute")
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS')")
 */
class JecouteSurveyController extends Controller
{
    /**
     * @Route("/survey", name="api_surveys_list", methods={"GET"})
     */
    public function surveyListAction(
        LocalSurveyRepository $localSurveyRepository,
        NationalSurveyRepository $nationalSurveyRepository,
        Serializer $serializer,
        UserInterface $user
    ): Response {
        /** @var Adherent $user */
        return new JsonResponse(
            $serializer->serialize(
                array_merge(
                    $localSurveyRepository->findAllByAdherent($user),
                    $nationalSurveyRepository->findAllPublished()
                ),
                'json',
                SerializationContext::create()->setGroups('survey_list')
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/survey/reply", name="api_survey_reply", methods={"POST"})
     */
    public function surveyReplyAction(
        Request $request,
        DataSurveyAnswerHandler $dataSurveyHandler,
        FormFactoryInterface $formFactory,
        UserInterface $user
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!SurveyTypeEnum::isValid($data['type'])) {
            throw new BadRequestHttpException('Invalid type.');
        }

        $form = $formFactory->create(DataSurveyFormType::class, null, [
            'csrf_protection' => false,
            'type' => $data['type'],
        ]);

        unset($data['type']);

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

        /** @var Adherent $user */
        $dataSurveyHandler->handle($form->getData(), $user);

        return new JsonResponse(['status' => 'ok'], JsonResponse::HTTP_CREATED);
    }

    private function getFormErrors(FormInterface $form)
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
