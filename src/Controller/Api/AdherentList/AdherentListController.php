<?php

namespace App\Controller\Api\AdherentList;

use App\Entity\Adherent;
use App\Exporter\ManagedUsersExporter;
use App\ManagedUsers\ManagedUsersFilter;
use App\ManagedUsers\ManagedUsersFilterFactory;
use App\Normalizer\ManagedUserNormalizer;
use App\Repository\Projection\ManagedUserRepository;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\Exception\ScopeQueryParamMissingException;
use App\Scope\FeatureEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/v3/adherents.{_format}', name: 'app_adherents_list_get', methods: ['GET'], requirements: ['_format' => 'json|csv|xls'], defaults: ['_format' => 'json'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AdherentListController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly ManagedUserRepository $repository,
        private readonly DenormalizerInterface $denormalizer,
        private readonly ManagedUsersExporter $exporter
    ) {
    }

    public function __invoke(Request $request, string $_format): Response
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        try {
            $this->authorizationChecker->isFeatureGranted($request, $user, [FeatureEnum::CONTACTS]);
        } catch (InvalidScopeException|ScopeQueryParamMissingException $e) {
            throw new BadRequestHttpException();
        } catch (ScopeExceptionInterface $e) {
            throw $this->createAccessDeniedException();
        }

        $scopeGenerator = $this->authorizationChecker->getScopeGenerator($request, $user);
        $scope = $scopeGenerator->generate($user);

        $filter = ManagedUsersFilterFactory::createForZones(
            $scopeGenerator->getCode(),
            $scope->getZones(),
            $scope->getCommitteeUuids()
        );

        $this->denormalizer->denormalize($request->query->all(), ManagedUsersFilter::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $filter,
            AbstractNormalizer::GROUPS => ['filter_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        $errors = $this->validator->validate($filter);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if ('json' !== $_format) {
            try {
                $this->authorizationChecker->isFeatureGranted($request, $user, [FeatureEnum::CONTACTS_EXPORT]);
            } catch (InvalidScopeException|ScopeQueryParamMissingException $e) {
                throw new BadRequestHttpException();
            } catch (ScopeExceptionInterface $e) {
                throw $this->createAccessDeniedException();
            }

            return $this->exporter->getResponse($_format, $filter);
        }

        $adherents = $this->repository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->json($adherents, Response::HTTP_OK, [], ['groups' => ['managed_user_read'], ManagedUserNormalizer::FILTER_PARAM => $filter]);
    }
}
