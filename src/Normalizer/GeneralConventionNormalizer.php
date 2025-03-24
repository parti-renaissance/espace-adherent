<?php

namespace App\Normalizer;

use App\Api\Serializer\PrivatePublicContextBuilder;
use App\Entity\Action\Action;
use App\Entity\Adherent;
use App\Entity\GeneralConvention\GeneralConvention;
use App\Repository\Action\ActionParticipantRepository;
use App\Security\Voter\CanManageActionVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GeneralConventionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @param GeneralConvention $object */
    public function normalize($object, $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $data = $this->normalizer->normalize($object, $format, $context + [__CLASS__ => true]);

        foreach ($this->getQuestions() as $key => $question) {
            if (!isset($data[$key])) {
                continue;
            }

            $data[$key] = trim($data[$key] ?? '');
            if (!empty($data[$key])) {
                $data[$key] = "**{$question}**\n\n".$data[$key];
            }
        }

        return $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [GeneralConvention::class => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__]) && $data instanceof GeneralConvention;
    }

    private function getQuestions(): array
    {
        return [
            'party_definition_summary' => 'Synthèse des réponses concernant l\'échange sur ce qu\'est un parti politique pour les participants.',
            'unique_party_summary' => 'Synthèse des échanges sur "Renaissance, un parti pas comme les autres".',
            'progress_since2016' => 'Synthèse des échanges sur le chemin parcouru depuis 2016.',
            'party_objectives' => 'Synthèse des échanges sur les objectifs de Renaissance.',
            'governance' => 'Notre gouvernance.',
            'communication' => 'Notre communication.',
            'militant_training' => 'La formation militante.',
            'member_journey' => 'Le parcours adhérent.',
            'mobilization' => 'La mobilisation.',
            'talent_detection' => 'Détecter les talents.',
            'election_preparation' => 'Préparer les élections.',
            'relationship_with_supporters' => 'Notre relation aux sympathisants, aux corps intermédiaires, à la société civile.',
            'work_with_partners' => 'Notre travail avec les partenaires.',
            'additional_comments' => 'Souhaitez-vous ajouter quelque chose ?',
        ];
    }
}
