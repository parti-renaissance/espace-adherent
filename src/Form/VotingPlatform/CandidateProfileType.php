<?php

namespace AppBundle\Form\VotingPlatform;

use AppBundle\Entity\CommitteeCandidacy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class, [
                'required' => false,
            ])
            ->add('croppedImage', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('biography', TextareaType::class, [
                'with_character_count' => true,
                'attr' => ['maxlength' => 2000],
                'filter_emojis' => true,
                'required' => false,
            ])
            ->add('save', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (!empty($data['croppedImage'])) {
                if (false !== strpos($data['croppedImage'], 'base64,')) {
                    $imageData = explode('base64,', $data['croppedImage'], 2);
                    $content = $imageData[1];
                    $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
                    file_put_contents($tmpFile, base64_decode($content));

                    $data['image'] = new UploadedFile(
                        $tmpFile,
                        'profile-image.png',
                        str_replace([';', 'data:'], '', $imageData[0]),
                        null,
                        null,
                        true
                    );

                    unset($data['croppedImage']);
                    $event->setData($data);
                } elseif (-1 == $data['croppedImage']) {
                    unset($data['croppedImage'], $data['image']);
                    $event->setData($data);
                    /** @var CommitteeCandidacy $model */
                    $model = $event->getForm()->getData();
                    $model->setRemoveImage(true);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommitteeCandidacy::class,
        ]);
    }
}
