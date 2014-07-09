<?php

namespace Kunstmaan\MediaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Kunstmaan\MediaBundle\Entity\Folder;
use Doctrine\ORM\EntityNotFoundException;

/**
 * FolderRepository
 */
class FolderRepository extends NestedTreeRepository
{
    /**
     * @param Folder $folder The folder
     */
    public function save(Folder $folder)
    {
        $em = $this->getEntityManager();
        $parent = $folder->getParent();
        $this->persistAsLastChildOf($folder, $parent);
        $em->flush();
    }

    /**
     * @param Folder $folder
     */
    public function delete(Folder $folder)
    {
        $em = $this->getEntityManager();

        $this->deleteMedia($folder, $em);
        $this->deleteChildren($folder, $em);
        $em->remove($folder);
        $em->flush();
    }

    /**
     * @param Folder $folder
     */
    public function deleteMedia(Folder $folder)
    {
        $em = $this->getEntityManager();

        foreach ($folder->getMedia() as $item) {
            $em->remove($item);
        }
    }

    /**
     * @param Folder $folder
     */
    public function deleteChildren(Folder $folder)
    {
        $em = $this->getEntityManager();

        foreach ($folder->getChildren() as $child) {
            $this->deleteMedia($child, $em);
            $this->deleteChildren($child, $em);
            $em->remove($child);
        }
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getAllFolders($limit = null)
    {
        $qb = $this->createQueryBuilder('folder')
          ->select('folder')
          ->where('folder.parent is null')
          ->orderBy('folder.name');

        if (false === is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $folderId
     *
     * @return object
     * @throws EntityNotFoundException
     */
    public function getFolder($folderId)
    {
        $folder = $this->find($folderId);
        if (!$folder) {
            throw new EntityNotFoundException('The id given for the folder is not valid.');
        }

        return $folder;
    }

    public function getFirstTopFolder()
    {
        $folder = $this->findOneBy(array('parent' => null, 'deleted' => false));
        if (!$folder) {
            throw new EntityNotFoundException('No first top folder found (where parent is NULL)');
        }

        return $folder;
    }

    public function getParentIds(Folder $folder)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getPathQueryBuilder($folder)
            ->select('node.id');

        $result = $qb->getQuery()->getScalarResult();
        $ids    = array_map('current', $result);

        return $ids;
    }

    public function childrenQueryBuilder($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false)
    {
        $qb = parent::childrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode);

        return $qb;
    }
}