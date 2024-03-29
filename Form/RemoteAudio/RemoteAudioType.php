<?php

namespace Kunstmaan\MediaBundle\Form\RemoteAudio;

use Kunstmaan\MediaBundle\Form\AbstractRemoteType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * RemoteAudioType
 */
class RemoteAudioType extends AbstractRemoteType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'media.form.remote_audio.type.label',
                    'choices' => ['soundcloud' => 'soundcloud'],
                    'constraints' => [new NotBlank()],
                    'required' => true,
                ]
            );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'kunstmaan_mediabundle_audiotype';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolver $resolver the resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Kunstmaan\MediaBundle\Helper\RemoteAudio\RemoteAudioHelper',
            ]
        );
    }
}
