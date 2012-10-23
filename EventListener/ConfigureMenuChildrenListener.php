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
        foreach($this->getParents($event->getMenu()) as $parent){
            $menuParentNames[] = $parent->getName();
        }
        if('Media' == $event->getMenu()->getName() or in_array('Media', $menuParentNames)){
            $currentFolderId = $this->request->get('folderId');
            if(isset($currentFolderId)){
                $currentFolder = $this->em->getRepository('KunstmaanMediaBundle:Folder')->findOneById($currentFolderId);
                $menuFolderId = $event->getMenu()->getExtra('folderId');
                $menuFolder = $this->em->getRepository('KunstmaanMediaBundle:Folder')->findOneById($menuFolderId);
                if (in_array($menuFolder, $currentFolder->getParents()) or $menuFolderId == $currentFolderId) {
                    foreach ($menuFolder->getChildren() as $child) {
                        $childMenu = $event->getMenu()->addChild($event->getFactory()->createItem($child->getName(), array('route' => 'KunstmaanMediaBundle_folder_show', 'routeParameters' => array('folderId' =>  $child->getId()))));
                        $childMenu->setExtra('folderId', $child->getId());
                        $childMenu->setAttribute('rel', $child->getRel());
                    }
                }
            }
        }
    }

    /**
     * Returns an array of the parents of the given ItemInterface
     *
     * @param ItemInterface           $menu
     * @param array                   $result
     *
     * @return array
     */
    public function getParents(ItemInterface $menu, $result = array()){
        $parent = $menu->getParent();
        if(!is_null($parent)){
            $result[] = $parent;
            $this->getParents($parent, $result);
        }
        return $result;
    }
}