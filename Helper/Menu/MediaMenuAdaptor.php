<?php

namespace Kunstmaan\MediaBundle\Helper\Menu;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\TopMenuItem;
use Kunstmaan\MediaBundle\Entity\Media;
use Kunstmaan\MediaBundle\Entity\Folder;

/**
 * The Media Menu Adaptor
 */
class MediaMenuAdaptor implements MenuAdaptorInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * In this method you can add children for a specific parent, but also remove and change the already created children
     *
     * @param MenuBuilder $menu      The MenuBuilder
     * @param MenuItem[]  &$children The current children
     * @param MenuItem    $parent    The parent Menu item
     * @param Request     $request   The Request
     */
    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        if (is_null($parent)) {
            // Add menu item for root gallery
            $rootFolders   = $this->em->getRepository('KunstmaanMediaBundle:Folder')->getRootNodes();
            $currentId     = $request->get('folderId');
            $currentFolder = null;
            if (isset($currentId)) {
                /* @var Folder $currentFolder */
                $currentFolder = $this->em->getRepository('KunstmaanMediaBundle:Folder')->findOneById($currentId);
            }

            foreach ($rootFolders as $rootFolder) {
                $menuItem = new TopMenuItem($menu);
                $menuItem
                    ->setRoute('KunstmaanMediaBundle_folder_show')
                    ->setRouteparams(array('folderId' => $rootFolder->getId()))
                    ->setInternalname($rootFolder->getName())
                    ->setParent(null)
                    ->setRole($rootFolder->getRel());

                if (!is_null($currentFolder)) {
                    $parentIds = $this->em->getRepository('KunstmaanMediaBundle:Folder')->getParentIds($currentFolder);
                    if (in_array($rootFolder->getId(), $parentIds)) {
                        $menuItem->setActive(true);
                    }
                }

                $children[] = $menuItem;
            }
        }
    }
}