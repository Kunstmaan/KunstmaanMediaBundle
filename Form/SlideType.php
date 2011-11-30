<?php

namespace Kunstmaan\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * class to define the form to upload a picture
 *
 */
class SlideType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('content', 'text')
            ->add('type', 'choice', array(
                'choices'   => array('speakerdeck' => 'speakerdeck', 'slideshare' => 'slideshare')))
        ;
    }

    public function getName()
    {
        return 'kunstmaan_mediabundle_slidetype';
    }
}

?>