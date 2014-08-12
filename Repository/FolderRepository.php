<?php

namespace Kunstmaan\MediaBundle\Repository;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Kunstmaan\MediaBundle\Entity\Folder;

/**
 * FolderRepository
 */
class FolderRepository extends NestedTreeRepository
{
    /**
     * @param Folder $folder The folder
     *
     * @throws \Exception
     */
    public function save(Folder $folder)
    {
        $em     = $this->getEntityManager();
        $parent = $folder->getParent();

        $em->beginTransaction();
        try {
            // Find where to insert the new item
            $children = $parent->getChildren();
            if (empty($children)) {
                // No children yet - insert as first child
                $this->persistAsFirstChildOf($folder, $parent);
            } else {
                $previousChild = null;
                foreach ($children as $child) {
                    // Alphabetical sorting - could be nice if we implemented a sorting strategy
                    if (strcasecmp($folder->getName(), $child->getName()) < 0) {
                        break;
                    }
                    $previousChild = $child;
                }
                if (is_null($previousChild)) {
                    $this->persistAsPrevSiblingOf($folder, $children[0]);
                } else {
                    $this->persistAsNextSiblingOf($folder, $previousChild);
                }
            }
            $em->commit();
            $em->flush();
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }
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
        $folder = $this->findOneBy(array('parent' => null));
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
}