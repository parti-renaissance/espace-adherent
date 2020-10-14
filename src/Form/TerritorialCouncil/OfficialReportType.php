<?php

namespace App\Form\TerritorialCouncil;

use App\Entity\TerritorialCouncil\OfficialReport;
use App\Entity\TerritorialCouncil\OfficialReportDocument;
use App\Form\ManagedPoliticalCommitteeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OfficialReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $report = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'filter_emojis' => true,
            ])
            ->add('politicalCommittee', ManagedPoliticalCommitteeChoiceType::class, [
                'required' => false,
                'disabled' => null !== $report->getId(),
            ])
            ->add('file', FileType::class, [
                'required' => null === $report->getId(),
                'attr' => [
                    'accept' => implode(',', OfficialReportDocument::MIME_TYPES),
                ],
                'constraints' => (null !== $report->getId()) ? [] : [
                    new Assert\NotNull([
                        'message' => 'territorail_council.official_report.no_file',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('data_class', OfficialReport::class);
    }
}
