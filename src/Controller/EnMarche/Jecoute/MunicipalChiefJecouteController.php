<?php

namespace AppBundle\Controller\EnMarche\Jecoute;

use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Intl\FranceCitiesBundle;
use AppBundle\Jecoute\JecouteSpaceEnum;
use AppBundle\Repository\Jecoute\LocalSurveyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-municipales-2020/jecoute", name="app_jecoute_municipal_chief_")
 *
 * @Security("is_granted('ROLE_MUNICIPAL_CHIEF') and user.municipalChiefManagedArea.hasJecouteAccess()")
 */
class MunicipalChiefJecouteController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::MUNICIPAL_CHIEF_SPACE;
    }

    protected function getLocalSurveys(LocalSurveyRepository $localSurveyRepository): array
    {
        return $localSurveyRepository->findAllByAuthor($this->getUser());
    }

    protected function createSurveyForm(LocalSurvey $localSurvey): FormInterface
    {
        if (!$localSurvey->getCity()) {
            $localSurvey->setCity(
                FranceCitiesBundle::getCityNameFromInseeCode(
                    current($this->getUser()->getMunicipalChiefManagedArea()->getCodes())
                )
            );
        }

        return parent::createSurveyForm($localSurvey)
            ->remove('concernedAreaChoice')
            ->remove('city')
        ;
    }

    protected function getSurveyTags(): array
    {
        return (array) substr(current($this->getUser()->getMunicipalChiefManagedArea()->getCodes()), 0, 2);
    }
}
