<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Entity\AdherentMessage\Filter\AudienceFilter;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'messages') and (data.getAuthor() == user or user.hasDelegatedFromUser(data.getAuthor(), 'messages'))"))]
class UpdateAdherentMessageFilterController extends AbstractController
{
    public function __construct(
        private readonly AdherentMessageManager $manager,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function __invoke(
        Request $request,
        AbstractAdherentMessage $data,
        ScopeGeneratorResolver $scopeGeneratorResolver,
    ): Response {
        if ($data->isSent()) {
            throw new BadRequestHttpException('This message has been already sent. You cannot update it.');
        }

        if (!$filter = $data->getFilter()) {
            $filter = new AudienceFilter();
        }

        $filter->reset();

        $this->serializer->deserialize($request->getContent(), $filter::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $filter,
            AbstractNormalizer::GROUPS => ['adherent_message_update_filter'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        $errors = $this->validator->validate($filter);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->manager->updateFilter($data, $filter);

        return $this->json('OK');
    }
}
