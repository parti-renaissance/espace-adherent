<?php

namespace App\Controller\EnMarche\ElectedRepresentative;

use App\ElectedRepresentative\Filter\ListFilter;
use App\Entity\Adherent;
use App\Entity\UserListDefinitionEnum;
use App\Form\ElectedRepresentative\ElectedRepresentativeFilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-la-republique-ensemble", name="app_lre_elected_representatives_")
 * @Security("is_granted('ROLE_LRE')")
 */
class LreElectedRepresentativeController extends AbstractElectedRepresentativeController
{
    protected function getSpaceType(): string
    {
        return 'lre';
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        if ($adherent->getLreArea()->isAllTags()) {
            return [];
        }

        return [$adherent->getLreArea()->getReferentTag()];
    }

    protected function createFilterForm(array $managedTags, ListFilter $filter = null): FormInterface
    {
        return $this->createForm(ElectedRepresentativeFilterType::class, $filter, [
            'referent_tags' => $managedTags,
            'user_list_definition_type' => [
                UserListDefinitionEnum::TYPE_ELECTED_REPRESENTATIVE,
                UserListDefinitionEnum::TYPE_LRE,
            ],
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
    }
}
