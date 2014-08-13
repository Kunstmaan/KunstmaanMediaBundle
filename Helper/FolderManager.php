<?php

namespace Kunstmaan\MediaBundle\Helper;

use Kunstmaan\MediaBundle\Entity\Folder;
use Kunstmaan\MediaBundle\Repository\FolderRepository;

class FolderManager
{
    /** @var FolderRepository $repository */
    private $repository;

    /**
     * @var FolderRepository $repository
     */
    public function __construct(FolderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getFolderHierarchy(Folder $rootFolder)
    {
        return $this->repository->childrenHierarchy($rootFolder);
    }

    public function getRootFolderFor(Folder $folder)
    {
        $parentIds = $this->getParentIds($folder);

        return $this->repository->getFolder($parentIds[0]);
    }

    public function getParentIds(Folder $folder)
    {
        return $this->repository->getParentIds($folder);
    }

    public function getParents(Folder $folder)
    {
        return $this->repository->getPath($folder);
    }

}