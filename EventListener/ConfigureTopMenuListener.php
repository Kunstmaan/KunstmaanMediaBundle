<?php

namespace Kunstmaan\MediaBundle\EventListener;

use Knp\Menu\ItemInterface;
use Doctrine\ORM\EntityManager;
use Kunstmaan\AdminBundle\Event\ConfigureTopMenuEvent;

class ConfigureTopMenuListener
{

    public function __construct()
    {

    }

    public function onTopMenuConfigure(ConfigureTopMenuEvent $event)
    {
        $menu = $event->getMenu();
        $menu->addChild('Media', array('route' => 'KunstmaanMediaBundle_folder_show', 'routeParameters' => array('folderId' => '1')));
    }
}