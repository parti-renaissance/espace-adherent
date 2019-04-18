<?php

namespace AppBundle\Controller\Api;

use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/form")
 */
class FormController extends Controller
{
    /**
     * @Route("/validate/{formType}", name="api_form_validate", condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function validate(Request $request, SerializerInterface $serializer, string $formType): Response
    {
        if (!is_subclass_of($formType, FormTypeInterface::class)) {
            throw $this->createNotFoundException('Form not found');
        }

        $form = $this->createForm($formType, null, ['csrf_protection' => false]);

        if (!$request->request->has($form->getName())) {
            throw new BadRequestHttpException(sprintf('No parameters with key "%s" founded.', $form->getName()));
        }

        $form->submit($request->request->get($form->getName()), false)->isValid();

        // We use jms serializer handler for errors.
        return new Response($serializer->serialize(
            [
                'children' => array_filter($form->all(), function (FormInterface $form) {
                    return $form->isSubmitted() && !$form->isValid();
                }),
            ],
            'json'
        ));
    }
}
