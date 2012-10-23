<?php

namespace Kunstmaan\MediaBundle\EventListener;

use Kunstmaan\AdminBundle\Event\ConfigureMenuChildrenEvent;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;

class ConfigureMenuChildrenListener
{

    /**
     * @var Request
     */
    private $request;

    private $em;

    public function __construct(Request $request, $em)
    {
        $this->request = $request;
        $this->em = $em;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function onMenuChildrenConfigure(ConfigureMenuChildrenEvent $event)
    {
        $menuParentNames = array();
        foreach ($this->getParents($event->getMenu()) as $parent) {
            $menuParentNames[] = $parent->getName();
        }
        if ('Media' == $event->getMenu()->getName() or in_array('Media', $menuParentNames)) {
            $menuFolderId = $event->getMenu()->getExtra('folderId');
            $menuFolder = $this->em->getRepository('KunstmaanMediaBundle:Folder')->findOneById($menuFolderId);
            foreach ($menuFolder->getChildren() as $child) {
                $childMenu = $event->getMenu()->addChild($event->getFactory()->createItem($child->getName(), array('route' => 'KunstmaanMediaBundle_folder_show', 'routeParameters' => array('folderId' =>  $child->getId()))));
                $childMenu->setExtra('folderId', $child->getId());
                $childMenu->setAttribute('rel', $child->getRel());
            }
        }
    }

    /**
     * Fills the array with the parents of the given ItemInterface
     * @param ItemInterface $menu
     *
     * @return array
     */
    public function getParents(ItemInterface $menu){
        $parent  = $menu->getParent();
        $parents = array();
        while ($parent != null) {
            $parents[] = $parent;
            $parent    = $parent->getParent();
        }

        return array_reverse($parents);
    }
}