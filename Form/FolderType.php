<?php

namespace Kunstmaan\MediaBundle\Form;

use Kunstmaan\MediaBundle\Entity\Folder;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;
use Kunstmaan\MediaBundle\Entity\File;

/**
 * FolderType
 */
class FolderType extends AbstractType
{
    /**
     * @var string
     */
    protected $entityname;

    /**
     * @var Folder
     */
    public $gallery;

    /**
     * @param string $name    The name
     * @param Folder $gallery The gallery
     */
    public function __construct($name, Folder $gallery = null)
    {
        $this->entityname = $name;
        $this->gallery = $gallery;
    }

    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gallery = $this->gallery;
        $type = $this;
        $builder
            ->add('name')
            ->add('parent', 'entity', array( 'class' => $this->getEntityName(), 'required' => false,
              'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use($gallery, $type) {
                  $qb = $er->createQueryBuilder('gallery');

                  if ($type->getEntityName()=="Kunstmaan\MediaBundle\Entity\Folder") {
                      $qb->where("gallery instance of 'Kunstmaan\MediaBundle\Entity\Folder'");
                  }

                  if ($gallery != null) {
                      $ids = "gallery.id != ". $gallery->getId();
                      $ids .= $type->addChildren($gallery);
                      $qb->andwhere($ids);
                  }

                  return $qb;
              }
        ));
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
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityname;
    }

    /**
     * @param Folder $gallery
     *
     * @return string
     */
    public function addChildren(Folder $gallery)
    {
        $ids = "";
        foreach ($gallery->getChildren() as $child) {
            $ids .= " and gallery.id != " . $child->getId();
            $ids .= $this->addChildren($child);
        }

        return $ids;
    }
}