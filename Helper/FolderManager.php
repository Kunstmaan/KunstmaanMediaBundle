<?php
/**
 * Created by Kunstmaan.
 * Date: 10/07/14
 * Time: 10:16
 */

namespace Kunstmaan\MediaBundle\Helper;

use Kunstmaan\MediaBundle\Entity\Folder;
use Kunstmaan\MediaBundle\Repository\FolderRepository;

class FolderManager
{
    /** @var FolderRepository $repository */
    private $repository;

    /** @var Folder $folder */
    private $folder;

    /** @var Folder[] $rootFolder */
    private $rootFolder;

    /** @var array */
    private $hierarchy;

    /** @var array */
    private $parentIds;

    /**
     * @var FolderRepository $repository
     */
    public function __construct(FolderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getFolderHierarchy(Folder $rootFolder)
    {
        if (!isset($this->hierarchy[$rootFolder->getId()])) {
            $this->hierarchy[$rootFolder->getId()] = $this->repository->childrenHierarchy($rootFolder);
        }

        return $this->hierarchy[$rootFolder->getId()];
    }

    public function getRootFolderFor(Folder $folder)
    {
        if (!isset($this->rootFolder[$folder->getId()])) {
            $parentIds = $this->getParentIds($folder);
            $this->rootFolder[$folder->getId()] = $this->repository->getFolder($parentIds[0]);
        }

        return $this->rootFolder[$folder->getId()];
    }

    public function getParentIds(Folder $folder)
    {
        if (!isset($this->parentIds[$folder->getId()])) {
            $this->parentIds[$folder->getId()] = $this->repository->getParentIds($folder);
        }

        return $this->parentIds[$folder->getId()];
    }

}