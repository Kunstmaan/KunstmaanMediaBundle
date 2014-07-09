<?php

namespace Kunstmaan\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class that defines a folder from the MediaBundle in the database
 *
 * @ORM\Entity(repositoryClass="Kunstmaan\MediaBundle\Repository\FolderRepository")
 * @ORM\Table(name="kuma_folders")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Folder extends AbstractEntity
{
    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    protected $locale;

    /**
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="children", fetch="EAGER")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent", fetch="LAZY")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $children;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Media", mappedBy="folder")
     */
    protected $media;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_at")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="updated_at")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $rel;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="internal_name", nullable=true)
     */
    protected $internalName;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @deprecated Using Gedmo SoftDeleteableInterface now
     */
    protected $deleted;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->media    = new ArrayCollection();
        $this->deleted = false;
    }

    /**
     * @param string $locale
     *
     * @return Folder
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @param string $rel
     *
     * @return Folder
     */
    public function setRel($rel)
    {
        $this->rel = $rel;

        return $this;
    }

    /**
     * Get createdAd
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAd
     *
     * @param \DateTime $createdAt
     *
     * @return Folder
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Folder
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Folder[]:
     */
    public function getParents()
    {
        $parent  = $this->getParent();
        $parents = array();
        while ($parent != null) {
            $parents[] = $parent;
            $parent    = $parent->getParent();
        }

        return array_reverse($parents);
    }

    /**
     * Get parent
     *
     * @return Folder
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent
     *
     * @param Folder $parent
     *
     * @return Folder
     */
    public function setParent(Folder $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Add a child
     *
     * @param Folder $child
     *
     * @return Folder
     */
    public function addChild(Folder $child)
    {
        $this->children[] = $child;
        $child->setParent($this);

        return $this;
    }

    /**
     * @param bool $deleted
     *
     * @return Folder
     * @deprecated
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Add file
     *
     * @param Media $media
     *
     * @return Folder
     */
    public function addMedia(Media $media)
    {
        $this->media[] = $media;

        return $this;
    }

    /**
     * Get media
     *
     * @return ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasActive($id)
    {
        foreach ($this->getChildren() as $child) {
            if ($child->hasActive($id) || $child->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Folder[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param array $children
     *
     * @return Folder
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return !is_null($this->deletedAt);
    }

    /**
     * @param \DateTime|null $deletedAt
     *
     * @return Folder
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @return string
     */
    public function getInternalName()
    {
        return $this->internalName;
    }

    /**
     * @param string $internalName
     *
     * @return Folder
     */
    public function setInternalName($internalName)
    {
        $this->internalName = $internalName;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Folder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}