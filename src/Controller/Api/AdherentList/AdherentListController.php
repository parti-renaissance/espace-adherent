<?php

declare(strict_types=1);

namespace App\Controller\Api\AdherentList;

use App\Entity\Adherent;
use App\Exporter\ManagedUsersExporter;
use App\ManagedUsers\ManagedUsersFilter;
use App\ManagedUsers\ManagedUsersFilterFactory;
use App\Normalizer\ImageExposeNormalizer;
use App\Normalizer\TranslateAdherentTagNormalizer;
use App\Repository\Projection\ManagedUserRepository;
use App\Scope\AuthorizationChecker;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\Exception\ScopeQueryParamMissingException;
use App\Scope\FeatureEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class AdherentListController extends AbstractController
{
    public function __construct(
        private readonly AuthorizationChecker $authorizationChecker,
        private readonly ManagedUserRepository $repository,
        private readonly DenormalizerInterface $denormalizer,
        private readonly ManagedUsersExporter $exporter,
    ) {
    }

    public function __invoke(Request $request, string $format): Response
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
            $scope->getCommitteeUuids(),
            $scope->getAgoraUuids(),
        );

        $this->denormalizer->denormalize($request->query->all(), ManagedUsersFilter::class, null, [
            AbstractNormalizer::OBJECT_TO_POPULATE => $filter,
            AbstractNormalizer::GROUPS => ['filter_write'],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ]);

        if ('json' !== $format) {
            try {
                $this->authorizationChecker->isFeatureGranted($request, $user, [FeatureEnum::CONTACTS_EXPORT]);
            } catch (InvalidScopeException|ScopeQueryParamMissingException $e) {
                throw new BadRequestHttpException();
            } catch (ScopeExceptionInterface $e) {
                throw $this->createAccessDeniedException();
            }

            return $this->exporter->getResponse($format, $filter);
        }

        $adherents = $this->repository->searchByFilter(
            $filter,
            $request->query->getInt('page', 1),
            min($request->query->getInt('page_size', 25), 200)
        );

        return $this->json(
            $adherents,
            Response::HTTP_OK,
            [],
            [
                'groups' => ['managed_users_list', ImageExposeNormalizer::NORMALIZATION_GROUP],
                TranslateAdherentTagNormalizer::ENABLE_TAG_TRANSLATOR => true,
            ]
        );
    }
}
