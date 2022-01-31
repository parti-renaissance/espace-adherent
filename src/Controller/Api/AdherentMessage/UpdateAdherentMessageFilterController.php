<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\Filter\FilterFactory;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Security("is_granted('ROLE_MESSAGE_REDACTOR') and (data.getAuthor() == user or user.hasDelegatedFromUser(data.getAuthor(), 'messages'))")
 */
class UpdateAdherentMessageFilterController extends AbstractController
{
    private $manager;
    private $serializer;
    private $validator;

    public function __construct(
        AdherentMessageManager $manager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->manager = $manager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function __invoke(
        Request $request,
        UserInterface $adherent,
        AbstractAdherentMessage $data,
        ScopeGeneratorResolver $scopeGeneratorResolver
    ): Response {
        if ($data->isSent()) {
            throw new BadRequestHttpException('This message has been already sent. You cannot update it.');
        }

        $scope = $scopeGeneratorResolver->generate();
        if ($scope && ($delegatedAccess = $scope->getDelegatedAccess())) {
            $adherent = $delegatedAccess->getDelegator();
        }

        if (!$filter = $data->getFilter()) {
            $filter = FilterFactory::create($adherent, $data->getType());
        }

        $this->serializer->deserialize($request->getContent(), \get_class($filter), JsonEncoder::FORMAT, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $filter,
            AbstractNormalizer::GROUPS => ['adherent_message_update_filter'],
        ]);

        $errors = $this->validator->validate($filter);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->manager->updateFilter($data, $filter);

        return $this->json('OK');
    }
}
