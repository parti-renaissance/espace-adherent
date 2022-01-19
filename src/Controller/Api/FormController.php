<?php

namespace App\Controller\Api;

use App\Normalizer\FormErrorNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/form/validate/{formType}", name="api_form_validate", condition="request.isXmlHttpRequest()", methods={"POST"})
 */
class FormController extends AbstractController
{
    public function __invoke(Request $request, string $formType): Response
    {
        if (!is_subclass_of($formType, FormTypeInterface::class)) {
            throw $this->createNotFoundException('Form not found');
        }

        $form = $this->createForm($formType, null, ['csrf_protection' => false]);

        if (!$request->request->has($form->getName())) {
            throw new BadRequestHttpException(sprintf('No parameters with key "%s" founded.', $form->getName()));
        }

        $form->submit($request->request->get($form->getName()), false)->isValid();

        return $this->json(['children' => array_filter($form->all(), function (FormInterface $form) {
            return $form->isSubmitted() && !$form->isValid();
        })], Response::HTTP_OK, [], ['groups' => [FormErrorNormalizer::GROUP]]);
    }
}
