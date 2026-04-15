<?php

declare(strict_types=1);

namespace App\Controller\Api\Filter;

use App\Entity\Adherent;
use App\JMEFilter\FiltersGenerator;
use App\OAuth\Model\Scope;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Cache(maxage: 3600, public: false)]
#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', ['contacts', 'messages', 'publications'])"))]
#[Route(path: '/v3/filters', name: 'app_collection_filters_get', methods: ['GET'])]
class GetCollectionFiltersController extends AbstractController
{
    public function __invoke(Request $request, ScopeGeneratorResolver $scopeGeneratorResolver, FiltersGenerator $builder, #[CurrentUser] Adherent $user): Response
    {
        if (!$feature = $request->query->get('feature')) {
            throw new BadRequestHttpException('Parameter "feature" is missing or empty');
        }

        if (!$scopeCode = $scopeGeneratorResolver->generate()?->getMainCode()) {
            throw new BadRequestHttpException('Parameter "scope" is missing or empty');
        }

        $isVox = $this->isGranted(Scope::generateRole(Scope::JEMARCHE_APP));

        return $this->json($builder->generate($user, $scopeCode, $feature, $isVox), context: ['groups' => ['filter:read']]);
    }
}
