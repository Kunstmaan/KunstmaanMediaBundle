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
        $menu = $event->getMenu();
        $factory = $event->getFactory();
        $request = $this->request;

        $menuParents = $this->getParents($menu);
        $menuParents[] = $menu;
        $menuParentNames = array();
        foreach($menuParents as $parent){
            $menuParentNames[] = $parent->getName();
        }
        if('Media' == $menu->getName() or in_array('Media', $menuParentNames)){
            $currentId = $request->get('folderId');
            if(isset($currentId)){
                $currentFolder = $this->em->getRepository('KunstmaanMediaBundle:Folder')->findOneById($currentId);
                $folderId = $menu->getExtra('folderId');
                $menuFolder = $this->em->getRepository('KunstmaanMediaBundle:Folder')->findOneById($folderId);

                $currentParents = $currentFolder->getParents();
                if (in_array($menuFolder, $currentParents) or $folderId == $currentId) {
                    $children = $menuFolder->getChildren();
                    foreach ($children as $child) {
                        $childId = $child->getId();
                        $childMenu = $menu->addChild($factory->createItem($child->getName(), array('route' => 'KunstmaanMediaBundle_folder_show', 'routeParameters' => array('folderId' => $childId))));
                        $childMenu->setExtra('folderId', $childId);
                    }
                }
            }
        }
    }

    public function getParents(ItemInterface $menu, $result = array()){
        $parent = $menu->getParent();
        if(!is_null($parent)){
            $result[] = $parent;
            $this->getParents($parent, $result);
        }
        return $result;
    }
}