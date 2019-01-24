<?php

namespace AppBundle\Controller\Api;

use AppBundle\Form\Jecoute\DataSurveyFormType;
use AppBundle\Repository\Jecoute\SurveyRepository;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/jecoute")
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JECOUTE_SURVEYS')")
 */
class JecouteSurveyController extends Controller
{
    /**
     * @Route("/survey", name="api_surveys_list")
     * @Method("GET")
     */
    public function surveyListAction(
        SurveyRepository $surveyRepository,
        Serializer $serializer,
        UserInterface $user
    ): Response {
        return new JsonResponse(
            $serializer->serialize(
                $surveyRepository->findAllByAdherent($user),
                'json',
                SerializationContext::create()->setGroups('survey_list')
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/survey/reply", name="api_survey_reply")
     * @Method("POST")
     */
    public function surveyReplyAction(Request $request, ObjectManager $manager): JsonResponse
    {
        $form = $this->createForm(DataSurveyFormType::class, null, [
            'csrf_protection' => false,
        ]);

        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $this->getFormErrors($form),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $dataSurvey = $form->getData();
        $dataSurvey->setAuthor($this->getUser());

        $manager->persist($dataSurvey);
        $manager->flush();

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
