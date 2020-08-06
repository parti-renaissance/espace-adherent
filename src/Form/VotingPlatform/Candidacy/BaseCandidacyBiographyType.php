<?php

namespace App\Form\VotingPlatform\Candidacy;

use App\Entity\VotingPlatform\Designation\BaseCandidacy;
use Ramsey\Uuid\Uuid;
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

class BaseCandidacyBiographyType extends AbstractType
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
                'attr' => ['maxlength' => 500],
                'filter_emojis' => true,
                'required' => false,
            ])
            ->add('save', SubmitType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();

            if (isset($data['skip'])) {
                unset($data['croppedImage'], $data['biography'], $data['image']);
            } elseif (!empty($data['croppedImage'])) {
                if (false !== strpos($data['croppedImage'], 'base64,')) {
                    $imageData = explode('base64,', $data['croppedImage'], 2);
                    $content = $imageData[1];
                    $tmpFile = tempnam(sys_get_temp_dir(), uniqid());
                    file_put_contents($tmpFile, base64_decode($content));

                    $data['image'] = new UploadedFile(
                        $tmpFile,
                        Uuid::uuid4()->toString().'.png',
                        str_replace([';', 'data:'], '', $imageData[0]),
                        null,
                        null,
                        true
                    );

                    unset($data['croppedImage']);
                } elseif (-1 == $data['croppedImage']) {
                    unset($data['croppedImage'], $data['image']);
                    /** @var BaseCandidacy $model */
                    $model = $event->getForm()->getData();
                    $model->setRemoveImage(true);
                }
            }

            $event->setData($data);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseCandidacy::class,
        ]);
    }
}
