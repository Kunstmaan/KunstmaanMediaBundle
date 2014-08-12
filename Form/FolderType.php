<?php

namespace Kunstmaan\MediaBundle\Form;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Kunstmaan\MediaBundle\Entity\Folder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * FolderType
 */
class FolderType extends AbstractType
{
    /**
     * @var Folder
     */
    public $folder;

    /**
     * @param Folder $folder The folder
     */
    public function __construct(Folder $folder = null)
    {
        $this->folder = $folder;
    }

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
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $folder = $this->folder;
        $builder
            ->add('name')
            ->add(
                'rel',
                'choice',
                array(
                    'choices' => array(
                        'media'     => 'media',
                        'image'     => 'image',
                        'slideshow' => 'slideshow',
                        'video'     => 'video'
                    ),
                )
            )
            ->add(
                'parent',
                'entity',
                array(
                    'class'         => 'KunstmaanMediaBundle:Folder',
                    'property'      => 'optionLabel',
                    'required'      => true,
                    'query_builder' => function (EntityRepository $er) use ($folder) {
                            /** @var QueryBuilder $qb */
                            $qb = $er->createQueryBuilder('f');
                            $qb->orderBy('folder.lft');

                            // Fetch all folders except the current one and its children
                            if (!is_null($folder) && $folder->getId() !== null) {
                                $orX = $qb->expr()->orX();
                                $orX
                                    ->add('folder.rgt > :right')
                                    ->add('folder.lft < :left');

                                $qb->where($orX)
                                    ->setParameter('left', $folder->getLeft())
                                    ->setParameter('right', $folder->getRight());
                            }

                            return $qb;
                        }
                )
            );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'kunstmaan_mediabundle_FolderType';
    }

    /**
     * Sets the default options for this type.
     *
     * @param OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Kunstmaan\MediaBundle\Entity\Folder',
            )
        );
    }
}